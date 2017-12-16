<?php
class Processes extends CI_Model {
    public function __construct() {
        parent::__construct();
        $this->load->library('DbUtil');
        $this->load->model('chart/BtnState');
        $this->load->model('MediaManager');
        $this->load->model('ChannelManager');
        $this->load->model('AdSlotManager');
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
            $strMethod = 'getallOriProfit'.$arrParams['source'];
            $arrOriData = $this->formatOriData($arrParams, $strMethod);
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
                $boolBtnRet = $this->checkBtnSumState($arrParams);
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
            $strMethod = 'getallOriProfit'.$arrParams['source'];
            $arrOriData = $this->formatOriData($arrParams, $strMethod);
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
        //calc slot sum()
        $intTime = time();
        $strSql = "UPDATE `tab_slot_user_profit_sum_daily` SET 
            `click_rate` = IF(pre_exposure_num=0,0,pre_click_num/pre_exposure_num), 
            `ecpm` = IF(pre_exposure_num=0,0,pre_profit/pre_exposure_num*1000),
            `update_time` = $intTime WHERE `date` = '".$arrParams['date']."'";
        $arrSlotRet = $this->dbutil->query($strSql);
        //do media sum
        $strMethod = 'getallUsrSlotSum';
        $arrSlotData = $this->formatSlotData($arrParams, $strMethod);
        $boolMediaSum = $this->UsrMediaSumOrNot($arrParams, $arrSlotData, "+");
        //do acct sum
        $strMethod = 'getallUsrMediaSum';
        $arrMediaData = $this->formatMediaData($arrParams, $strMethod);
        $boolAcctSum = $this->UsrAcctSumOrNot($arrParams, $arrMediaData, "+");

        //更新btn
        if($boolMediaSum && $boolAcctSum) {
            //更新btn & 置已汇总标记//TODO 加字段 
                $arrStateWhere = [
                    'btn_load' => 0,
                    'btn_load_cancel' => 0,
                    'btn_select' => 0,
                    'btn_sum' => 0,
                    'btn_sum_cancel' => 1,
                    'where' => "date='".$arrParams['date']."'",
                ];
                $arrBtnState = $this->BtnState->updateBtnState($arrStateWhere);
                return true;
        }


        return true;

    }//}}}//

    public function doCancelSummary($arrParams) {//{{{//
        //del Acct daily
        $arrParams['method'] = 'delUsrAcctSum';
        $boolAcct = $this->delAcctDaily($arrParams);

        //del media daily
        $arrParams['method'] = 'delUsrMediaSum';
        $boolMedia = $this->delMediaDaily($arrParams);

        //del slot daily
        $arrParams['method'] = 'delUsrSlotSum';
        $boolSlot = $this->delSlotDaily($arrParams);

        //del ori daily
        $arrParams['method'] = 'delOriProfitBaiDu';
        $boolOriBaiDu = $this->delOriDaily($arrParams);
        $arrParams['method'] = 'delOriProfitGdt';
        $boolOriGdt = $this->delOriDaily($arrParams);
        $arrParams['method'] = 'delOriProfitTuia';
        $boolOriTuia = $this->delOriDaily($arrParams);
        $arrParams['method'] = 'delOriProfitYezi';
        $boolOriYezi = $this->delOriDaily($arrParams);

        //reset btn state
        if($boolAcct && $boolMedia 
            && $boolSlot && $boolOriBaiDu
            && $boolOriGdt && $boolOriTuia && $boolOriYezi) {
            $arrStateWhere = [
                'btn_load' => 0,
                'btn_load_cancel' => 0,
                'btn_select' => 1,
                'btn_sum' => 0,
                'btn_sum_cancel' => 0,
                'where' => "date='".$arrParams['date']."'",
            ];
            $arrBtnState = $this->BtnState->updateBtnState($arrStateWhere);
            return true;
        }
        return false;
    }//}}}//

    /**
     * formatOriData
     */
    private function formatOriData($arrParams, $strMethod) {//{{{//
        //get all data, then do summary
        $arrSelect = [
            'select' => '*',
            'where' => "mark=1 AND date='".$arrParams['date']."'",
            'order_by' => 'create_time DESC',
        ];
        $arrRes = $this->dbutil->$strMethod($arrSelect);
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

    /**
     * formatSlotData
     */
    private function formatSlotData($arrParams, $strMethod) {//{{{//
        //get all data, then do summary
        $arrSelect = [
            'select' => '*',
            'where' => "mark=1 AND date='".$arrParams['date']."'",
            'order_by' => 'create_time DESC',
        ];
        $arrRes = $this->dbutil->$strMethod($arrSelect);
        if(empty($arrRes)) {
            return false;
        }
        $arrOriData = [];
        foreach($arrRes as $key=>$val) {
            $arrOriData[$val['app_id']]['app_id'] = $val['app_id'];
            $arrOriData[$val['app_id']]['account_id'] = $val['acct_id'];
            $arrOriData[$val['app_id']]['pre_exposure_num'] = empty($arrOriData[$val['app_id']]['pre_exposure_num'])
                ? intval($val['pre_exposure_num']) : intval($val['pre_exposure_num']) + $arrOriData[$val['app_id']]['pre_exposure_num'];
            $arrOriData[$val['app_id']]['post_exposure_num'] = empty($arrOriData[$val['app_id']]['post_exposure_num'])
                ? intval($val['post_exposure_num']) : intval($val['post_exposure_num']) + $arrOriData[$val['app_id']]['post_exposure_num'];
            $arrOriData[$val['app_id']]['pre_click_num'] = empty($arrOriData[$val['app_id']]['pre_click_num'])
                ? intval($val['pre_click_num']) : intval($val['pre_click_num']) + $arrOriData[$val['app_id']]['pre_click_num'];
            $arrOriData[$val['app_id']]['post_click_num'] = empty($arrOriData[$val['app_id']]['post_click_num'])
                ? intval($val['post_click_num']) : intval($val['post_click_num']) + $arrOriData[$val['app_id']]['post_click_num'];
            $arrOriData[$val['app_id']]['pre_profit'] = empty($arrOriData[$val['app_id']]['pre_profit'])
                ? intval($val['pre_profit']) : intval($val['pre_profit']) + $arrOriData[$val['app_id']]['pre_profit'];
            $arrOriData[$val['app_id']]['post_profit'] = empty($arrOriData[$val['app_id']]['post_profit'])
                ? floatval($val['post_profit']) : floatval($val['post_profit']) + $arrOriData[$val['app_id']]['post_profit'];
            $arrOriData[$val['app_id']]['click_rate'] = 0;
            $arrOriData[$val['app_id']]['cpc'] = 0;
            $arrOriData[$val['app_id']]['ecpm'] = 0;
            $arrOriData[$val['app_id']]['mark'] = 1;
            $arrOriData[$val['app_id']]['date'] = $val['date'];
            $arrOriData[$val['app_id']]['create_time'] = time();
            $arrOriData[$val['app_id']]['update_time'] = time();

        }
        return $arrOriData;
    }//}}}//

    /**
     * formatMediaData
     */
    private function formatMediaData($arrParams, $strMethod) {//{{{//
        //get all data, then do summary
        $arrSelect = [
            'select' => '*',
            'where' => "mark=1 AND date='".$arrParams['date']."'",
            'order_by' => 'create_time DESC',
        ];
        $arrRes = $this->dbutil->$strMethod($arrSelect);
        if(empty($arrRes)) {
            return false;
        }
        $arrOriData = [];
        foreach($arrRes as $key=>$val) {
            $arrOriData[$val['account_id']]['account_id'] = $val['account_id'];
            $arrOriData[$val['account_id']]['pre_exposure_num'] = empty($arrOriData[$val['account_id']]['pre_exposure_num'])
                ? intval($val['pre_exposure_num']) : intval($val['pre_exposure_num']) + $arrOriData[$val['account_id']]['pre_exposure_num'];
            $arrOriData[$val['account_id']]['post_exposure_num'] = empty($arrOriData[$val['account_id']]['post_exposure_num'])
                ? intval($val['post_exposure_num']) : intval($val['post_exposure_num']) + $arrOriData[$val['account_id']]['post_exposure_num'];
            $arrOriData[$val['account_id']]['pre_click_num'] = empty($arrOriData[$val['account_id']]['pre_click_num'])
                ? intval($val['pre_click_num']) : intval($val['pre_click_num']) + $arrOriData[$val['account_id']]['pre_click_num'];
            $arrOriData[$val['account_id']]['post_click_num'] = empty($arrOriData[$val['account_id']]['post_click_num'])
                ? intval($val['post_click_num']) : intval($val['post_click_num']) + $arrOriData[$val['account_id']]['post_click_num'];
            $arrOriData[$val['account_id']]['pre_profit'] = empty($arrOriData[$val['account_id']]['pre_profit'])
                ? intval($val['pre_profit']) : intval($val['pre_profit']) + $arrOriData[$val['account_id']]['pre_profit'];
            $arrOriData[$val['account_id']]['post_profit'] = empty($arrOriData[$val['account_id']]['post_profit'])
                ? floatval($val['post_profit']) : floatval($val['post_profit']) + $arrOriData[$val['account_id']]['post_profit'];
            $arrOriData[$val['account_id']]['click_rate'] = 0;
            $arrOriData[$val['account_id']]['cpc'] = 0;
            $arrOriData[$val['account_id']]['ecpm'] = 0;
            $arrOriData[$val['account_id']]['mark'] = 1;
            $arrOriData[$val['account_id']]['date'] = $val['date'];
            $arrOriData[$val['account_id']]['create_time'] = time();
            $arrOriData[$val['account_id']]['update_time'] = time();

        }
        return $arrOriData;
    }//}}}//

    /**
     * slot sum
     */
    private function UsrSlotSumOrNot($arrParams, $arrOriData, $label) {//{{{//
        if($arrOriData == false) {
            return false;
        }
        foreach($arrOriData as $key=>$val) {
            $arrSlot = $this->AdSlotManager->getSlotBySlotId($key);
            if(!$arrSlot) {
                continue;
            }
            $val['slot_name'] = $arrSlot['slot_name'];

			$time = time();
            $sqlKeys = " (`".implode("`, `", array_keys($val))."`)";
            $sqlString = '('."'".implode( "','", $val ) . "'".')'; //批量
            $insertRows[] = $sqlString;
            $strValues = implode(',', $insertRows);
			$sql = "INSERT INTO tab_slot_user_profit_sum_daily {$sqlKeys} 
 VALUES {$strValues} ON DUPLICATE KEY UPDATE 
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
            unset($insertRows);
		}
        return true; 
	}//}}}//
    /**
     * media sum
     */
    private function UsrMediaSumOrNot($arrParams, $arrOriData, $label) {//{{{//
        if($arrOriData == false) {
            return false;
        }
        foreach($arrOriData as $key=>$val) {
            $arrMedia = $this->MediaManager->getMediaByAppId($key);
            if(!$arrMedia) {
                return false;
            }
            $val['media_name'] = $arrMedia[0]['media_name'];
            $val['platform'] = $arrMedia[0]['media_platform'];
			$time = time();
            $sqlKeys = " (`".implode("`, `", array_keys($val))."`)";
            $sqlString = '('."'".implode( "','", $val ) . "'".')'; //批量
            $insertRows[] = $sqlString;
            $strValues = implode(',', $insertRows);
			$sql = "INSERT IGNORE INTO tab_media_user_profit_sum_daily {$sqlKeys}
  VALUES {$strValues} ON DUPLICATE KEY UPDATE 
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
            unset($insertRows);
		}
        return true; 
	}//}}}//

    /**
     * acct sum
     */
    private function UsrAcctSumOrNot($arrParams, $arrOriData, $label) {//{{{//
        if($arrOriData == false) {
            return false;
        }
        foreach($arrOriData as $key=>$val) {
            $arrAcctInfo = $this->ChannelManager->getAcctByAcctId($key);
            if(!$arrAcctInfo) {
                return false;
            }

            $val['acct_name'] = $arrAcctInfo['company'];
            $sqlKeys = " (`".implode("`, `", array_keys($val))."`)";
			$time = time();
            $sqlString = '('."'".implode( "','", $val ) . "'".')'; //批量
            $insertRows[] = $sqlString;
            $strValues = implode(',', $insertRows);
			$sql = "INSERT IGNORE INTO tab_acct_user_profit_sum_daily {$sqlKeys}
 VALUES {$strValues} ON DUPLICATE KEY UPDATE 
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
            unset($insertRows);
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
        if(empty($arrParams['source'])) {
            $arrSelect = [
                'select' => 'btn_select,btn_load,btn_load_cancel,btn_sum,btn_sum_cancel,platform_en,date',
                'where' => "date='".$arrParams['date']."'",
                'limit' => '0,1',
            ];
        } else {
            $arrSelect = [
                'select' => 'btn_select,btn_load,btn_load_cancel,btn_sum,btn_sum_cancel,platform_en,date',
                'where' => "platform_en='".$arrParams['source']."' AND date='".$arrParams['date']."'",
            ];
        }
        $arrRes = $this->BtnState->getBtnState($arrSelect);
        return $arrRes;
    }//}}}//

    public function checkBtnSumState($arrParams) {//{{{//
        $boolRet = false;
        $arrSelect = [
            'select' => '*',
            'where' => "date='".$arrParams['date']."'",
        ];
        $arrRes = $this->BtnState->getBtnState($arrSelect);
        if(count($arrRes) == 2) {//TODO change->4
             $arrWhere = [
                'btn_sum' => 1,
                'where' => "date='".$arrParams['date']."'",
            ];
            $boolRet = $this->BtnState->updateBtnState($arrWhere);           
            return true;
        }
        return $boolRet;
    }//}}}//

    /**
     * delAcctDaily
     */
    private function delAcctDaily($arrParams) {//{{{//
        $arrWhere = [
            'where' => "date= '". $arrParams['date'] ."'",
        ];
        $method = $arrParams['method'];
        $arrRes = $this->dbutil->$method($arrWhere);
        return $arrRes;
    }//}}}//

    /**
     * delMediaDaily
     */
    private function delMediaDaily($arrParams) {//{{{//
        $arrWhere = [
            'where' => "date= '". $arrParams['date'] ."'",
        ];
        $method = $arrParams['method'];
        $arrRes = $this->dbutil->$method($arrWhere);
        return $arrRes;
    }//}}}//

    /**
     * delSlotDaily
     */
    private function delSlotDaily($arrParams) {//{{{//
        $arrWhere = [
            'where' => "date= '". $arrParams['date'] ."'",
        ];
        $method = $arrParams['method'];
        $arrRes = $this->dbutil->$method($arrWhere);
        return $arrRes;
    }//}}}//

    /**
     * delOriDaily
     */
    private function delOriDaily($arrParams) {//{{{//
        $arrWhere = [
            'where' => "date= '". $arrParams['date'] ."'",
        ];
        $method = $arrParams['method'];
        $arrRes = $this->dbutil->$method($arrWhere);
        return $arrRes;
    }//}}}//
}
