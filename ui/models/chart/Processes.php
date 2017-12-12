<?php
class Processes extends CI_Model {
    public function __construct() {
        parent::__construct();
        $this->load->library('DbUtil');
        $this->load->model('chart/BtnState');
    }

    public function doConfirmLoad($arrParams) {//{{{//
        $arrSelect = [
            'select' => '*',
            'where' => "platform_en='".$arrParams['source']."' AND btn_load=1 AND date='".$arrParams['date']."'",
        ];
        $arrStateRet = $this->BtnState->checkBtnState($arrSelect);
        if(count($arrStateRet) == 1) {
            //更新mark
            $arrWhere = array(
                'mark' => 1,
                'where' => "date='".$arrParams['date']."'",
            );
            $method = 'udpOriProfit'.$arrParams['source'];
            $arrMarkRet = $this->dbutil->$method($arrWhere);
            //do sum
            $arrOriData = $this->formatOriData($arrParams);
            $boolSum = $this->UsrSlotSumOrNot($arrParams, $arrOriData, "+");

            //更新btn & 置已汇总标记//TODO 加字段 
            if($arrMarkRet && $boolSum) {
                $arrStateWhere = [
                    'btn_load' => 0,
                    'btn_load_cancel' => 1,
                    'btn_select' => 0,
                    'where' => "date='".$arrParams['date']."' AND platform_en='".$arrParams['source']."'",
                ];
                $arrBtnState = $this->BtnState->updateBtnState($arrStateWhere);
                return true;
            }
        }

        return false;

    }//}}}//

    public function doCancelLoad($arrParams) {//{{{//
        $arrSelect = [
            'select' => '*',
            'where' => "platform_en='".$arrParams['source']."' AND btn_load_cancel=1 AND date='".$arrParams['date']."'",
        ];
        $arrStateRet = $this->BtnState->checkBtnState($arrSelect);
        if(count($arrStateRet) == 1) {
            //删除广告位汇总表相关数据和原始表
            //do cancel sum
            $arrOriData = $this->formatOriData($arrParams);
            $boolNoSum = $this->UsrSlotSumOrNot($arrParams, $arrOriData, "-");
            $boolDel = $this->delOriProfitDaily($arrParams, 1);

            //更新btn & 置已汇总标记//TODO 加字段 
            if($boolDel && $boolNoSum) {
                $arrStateWhere = [
                    'btn_load' => 0,
                    'btn_load_cancel' => 0,
                    'btn_select' => 1,
                    'where' => "date='".$arrParams['date']."' AND platform_en='".$arrParams['source']."'",
                ];
                $arrBtnState = $this->BtnState->updateBtnState($arrStateWhere);
                return true;
            }
        }

        return false;

    }//}}}//

    public function doSummary($arrParams) {//{{{//
        //check btn_sum state
        $boolBtnRet = $this->checkBtnSumState($arrParams);
        if(!$boolBtnRet) {
            return false;
        }
        //do sum

    }//}}}//

    public function doCancelSummary($arrParams) {//{{{//
    }//}}}//

    /**
     * formatOriData
     */
    private function formatOriData($arrParams) {//{{{//
        //get all data, then do summary
        $arrSelect = [
            'select' => '*',
            'where' => "mark=1 AND date='".$arrParams['date']."'",
            'order_by' => 'create_time DESC',
        ];
        $method = 'getallOriProfit'.$arrParams['source'];
        $arrRes = $this->dbutil->$method($arrSelect);
        if(empty($arrRes)) {
            return false;
        }
        $arrOriData = [];
        foreach($arrRes as $key=>$val) {
            $arrOriData[$val['user_slot_id']]['user_slot_id'] = $val['user_slot_id'];
            $arrOriData[$val['user_slot_id']]['app_id'] = $val['app_id'];
            $arrOriData[$val['user_slot_id']]['acct_id'] = $val['acct_id'];
            $arrOriData[$val['user_slot_id']]['pre_exposure_num'] = empty($arrOriData[$val['user_slot_id']]['pre_exposure_num'])
                ? intval($val['pre_exposure_num']) : intval($val['pre_exposure_num']) + $arrOriData[$val['user_slot_id']]['pre_exposure_num'];
            $arrOriData[$val['user_slot_id']]['post_exposure_num'] = empty($arrOriData[$val['user_slot_id']]['post_exposure_num'])
                ? intval($val['post_exposure_num']) : intval($val['post_exposure_num']) + $arrOriData[$val['user_slot_id']]['post_exposure_num'];
            $arrOriData[$val['user_slot_id']]['pre_click_num'] = empty($arrOriData[$val['user_slot_id']]['pre_click_num'])
                ? intval($val['pre_click_num']) : intval($val['pre_click_num']) + $arrOriData[$val['user_slot_id']]['pre_click_num'];
            $arrOriData[$val['user_slot_id']]['post_click_num'] = empty($arrOriData[$val['user_slot_id']]['post_click_num'])
                ? intval($val['post_click_num']) : intval($val['post_click_num']) + $arrOriData[$val['user_slot_id']]['post_click_num'];
            $arrOriData[$val['user_slot_id']]['pre_profit'] = empty($arrOriData[$val['user_slot_id']]['pre_profit'])
                ? intval($val['pre_profit']) : intval($val['pre_profit']) + $arrOriData[$val['user_slot_id']]['pre_profit'];
            $arrOriData[$val['user_slot_id']]['post_profit'] = empty($arrOriData[$val['user_slot_id']]['post_profit'])
                ? floatval($val['post_profit']) : floatval($val['post_profit']) + $arrOriData[$val['user_slot_id']]['post_profit'];
            $arrOriData[$val['user_slot_id']]['click_rate'] = 0;
            $arrOriData[$val['user_slot_id']]['cpc'] = 0;
            $arrOriData[$val['user_slot_id']]['ecpm'] = 0;
            $arrOriData[$val['user_slot_id']]['mark'] = 1;
            $arrOriData[$val['user_slot_id']]['date'] = $val['date'];
            $arrOriData[$val['user_slot_id']]['create_time'] = time();
            $arrOriData[$val['user_slot_id']]['update_time'] = time();

        }
        return $arrOriData;
    }//}}}//

    private function UsrSlotSumOrNot($arrParams, $arrOriData, $label) {//{{{//
        if($arrOriData == false) {
            return false;
        }
        foreach($arrOriData as $key=>$val) {
			$time = time();
            $sqlString = '('."'".implode( "','", $val ) . "'".')'; //批量
            $insertRows[] = $sqlString;
            $strValues = implode(',', $insertRows);
			$sql = "INSERT IGNORE INTO tab_slot_user_profit_sum_daily(
                user_slot_id,app_id,acct_id,pre_exposure_num,
                post_exposure_num,pre_click_num,post_click_num,pre_profit,post_profit,click_rate,cpc,ecpm,
                mark,date,create_time,update_time) VALUES {$strValues} ON DUPLICATE KEY UPDATE 
                pre_exposure_num=pre_exposure_num $label '" . $val['pre_exposure_num'] . "',
                post_exposure_num=post_exposure_num $label '" . $val['post_exposure_num'] ."',
                pre_click_num=pre_click_num $label '" . $val['pre_click_num'] ."',
                post_click_num=post_click_num $label '" . $val['post_click_num'] ."',
                pre_profit=pre_profit $label '". $val['pre_profit'] ."',
                post_profit=post_profit $label '" . $val['post_profit'] ."',
                update_time='". $time ."'";
			$boolRes = $this->dbutil->query($sql);
			if(!$boolRes) {
				return false;
			}
		}
        return true; 
	}//}}}//
    /**
     * delUsrSlotSum
     */
    private function delUsrSlotSum($arrParams) {//{{{//
    }//}}}//

    /**
     * delOriProfitDaily where mark=0
     */
    public function delOriProfitDaily($arrParams, $sign='0') {//{{{//
        $intSign = intval($sign);
        $arrWhere = [
            'where' => "mark=$intSign AND date= '". $arrParams['date'] ."'",
        ];
        $method = "delOriProfit".$arrParams['source'];
        $arrRes = $this->dbutil->$method($arrWhere);
        return $arrRes;
    }//}}}//

    public function getBtnState($arrParams) {//{{{//
        $arrSelect = [
            'select' => '*',
            'where' => "user=".$arrParams['account_id']." AND date='".$arrParams['date']."'",
        ];
        $arrRes = $this->BtnState->getBtnState($arrSelect);
        return $arrRes;
    }//}}}//
}
