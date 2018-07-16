<?php
class CsvAdapter extends CI_Model {
	public function __construct() {
		parent::__construct();
		$this->load->library('CsvReader');
        $this->load->library('DbUtil');
        $this->load->model('chart/BtnState');
        $this->load->model('chart/Processes');
        $this->discount = 0.8;
	}

	public function process($arrParams) {//{{{//
        $arrSelect = [
            'select' => 'btn_select',
            'where' => "platform_en='".$arrParams['source']."' AND date='".$arrParams['date']."'",
        ];
        $arrStateRet = $this->BtnState->checkBtnState($arrSelect);
        if($arrStateRet && $arrStateRet[0]['btn_select'] == 0) {
            return false;
        }
        $boolDataRet = false;
		$boolRet = $this->csvreader->import();
		$arrContent = $this->csvreader->read_file();
		if($boolRet == true) {
			$arrContent = $this->csvreader->read_file();
            $method = "process".$arrParams['source']."Content";
            $boolDataRet = $this->$method($arrContent, $arrParams);
		}
        return $boolDataRet ? $boolDataRet : false;
	}//}}}//

	private function getChargingNameMaster($chargingName){
		$where = array(
			'select' => 'account_id',
			'where' => 'charging_name="'.$chargingName.'"',
		);

		$res = $this->dbutil->getChargingList($where);
		if(empty($res)){
			return false;
		}

		return $res[0]['account_id'];
	}

	private function processChargingContent($arrContent, $arrParams){
		$chunkData = array_chunk($arrContent, 5000);
		$count = count($chunkData[0]);

		$insertRows = array();
		foreach($chunkData[0] as $k => $value){
			if($k == 0) {
				continue;
			}

			$string = mb_convert_encoding(trim(strip_tags($value)), 'utf-8', 'gbk');
			$v = explode(',', trim($string));
			$row = array();
			$date = date("Y-m-d",strtotime($v[0]));
			if(!$date || empty($date)){
				continue;
			}
			$accountId = $this->getChargingNameMaster($v['1']);
			if($accountId == false){
				continue;
			}
			$row['date'] = $date;
			$row['account_id'] = $accountId;
			$row['charging_name'] = $v['1'];
			$row['search_num'] = $v['2'];
			$row['click_num'] = $v['3'];
			$row['click_rate'] = (intval($v[2]) == 0) ? 0 : round($v[3]/$v[2]*100, 3);
			$row['money'] = $v['4'];
			$row['mark'] = '1';
			$row['create_time'] = time();
			$row['update_time'] = time();
			$sqlString = '('."'".implode( "','", $row ) . "'".')'; //批量
			$insertRows[] = $sqlString;
		}


		$strValues = implode(',', $insertRows);

		$sql = "INSERT IGNORE INTO charging_data_daily(account_id,charging_name,search_num,click_num,click_rate,money,date,create_time,update_time) VALUES {$strValues}";
		$boolRes = $this->dbutil->query($sql);
		unset($insertRows);

		return $boolRes;
	}

    /**
     * processBAIDUContent
     */
    private function processBAIDUContent($arrContent, $arrParams) {//{{{//
        $chunkData = array_chunk($arrContent , 5000);
		$count = count($chunkData);
		for ($i = 0; $i < $count; $i++) {
			$insertRows = array();
			foreach($chunkData[$i] as $k => $value){
                if($k == 0) {
                    continue;
                }
				$string = mb_convert_encoding(trim(strip_tags($value)), 'utf-8', 'gbk');
				$v = explode(',', trim($string));
				$row = array();
				$row['ori_slot_id'] = $v[0];
                $arrUserSlotInfo = $this->getUserSlotInfo($row['ori_slot_id']);
                if($arrUserSlotInfo == false) {
                    continue;
                }
                // get discount

                $arrMedia = $this->MediaManager->getMediaByAppId($arrUserSlotInfo['app_id']);
                if(!$arrMedia) {
                    $this->discount = 0.8;
                }
                $this->discount = intval($arrMedia[0]['proportion'])/100;

                $row['user_slot_id'] = $arrUserSlotInfo['slot_id'];
                $row['app_id'] = $arrUserSlotInfo['app_id'];
                $row['acct_id'] = $arrUserSlotInfo['account_id'];
				$row['ori_slot_name'] = $v[1];
				$row['ori_slot_type'] = $v[2];
				$row['pre_exposure_num'] = intval($v[3]);
				$row['post_exposure_num'] = intval($v[3]) * $this->discount;
                $row['pre_click_num'] = intval($v[4]);
                $row['post_click_num'] = intval($v[4]) * $this->discount;
				$row['pre_profit'] = floatval($v[5]);
				$row['post_profit'] = floatval($v[5]) * $this->discount;
				$row['click_rate'] = (intval($v[3]) == 0) ? 0 : round($v[4]/$v[3]*100, 3);
				$row['cpc'] = (intval($v[4]) == 0) ? 0 : round($v[5]/$v[4]*100, 3);
				$row['ecpm'] = (intval($v[3]) == 0) ? 0 : round($v[5]/$v[3]*1000, 3);
                // re calc post_exposure_num & post_click_num
                $row['post_exposure_num'] = $row['ecpm'] == 0 ? 0 : intval($row['post_profit'] * 1000 / $row['ecpm']);
                $row['post_click_num'] = intval($row['post_exposure_num'] * $row['click_rate'] / 100);

				$row['mark'] = 0;
                $row['date'] = $arrParams['date'];
                $row['create_time'] = time();
                $row['update_time'] = time();
				$sqlString = '('."'".implode( "','", $row ) . "'".')'; //批量
				$insertRows[] = $sqlString;
			}
            // 导入前先清空之前的数据 mark=0
            $this->Processes->delOriProfitDaily($arrParams);
			$result = $this->addDetail($insertRows, 'tab_slot_ori_profit_baidu_daily'); //批量将sql插入数据库。
            unset($insertRows);
            // 更新按钮状态
            if($result) {
                //todo user info
                $arrState['user'] = 1;//$this->arrUser['account_id'];
                $arrState['btn_load'] = 1;
                $arrState['btn_load_cancel'] = 0;
                $arrState['btn_sum'] = 0;
                $arrState['btn_sum_cancel'] = 0;
                $arrState['create_time'] = time();
                $arrState['update_time'] = time();
                $arrState['platform_en'] = $arrParams['source'];
                $arrState['btn_select'] = 1;
                $arrState['date'] = $arrParams['date'];
                $arrStateRet = $this->BtnState->insertBtnState($arrState);
            }

            return $result;
		}
	}//}}}//

    /**
     * processGDTContent //TODO:格式不确定
     */
    private function processGDTContent($arrContent, $arrParams) {//{{{//
        $chunkData = array_chunk($arrContent , 5000);
		$count = count($chunkData);
		for ($i = 0; $i < $count; $i++) {
			$insertRows = array();
			foreach($chunkData[$i] as $k => $value){
                //var_dump($k,$value);
                if($k == 0) {
                    continue;
                }
				$string = mb_convert_encoding(trim(strip_tags($value)), 'utf-8', 'gbk');
                $regex="/(\".*?),(.*?\")/i";
                $replace = '$1$2';
                $v = explode(',', trim(preg_replace($regex, $replace, $string)));
				//$v = explode(',', trim($string));
                $v[3] = intval(trim($v[3], '"'));
                $v[4] = intval(trim($v[4], '"'));
                $v[5] = floatval(trim($v[5], '"'));
				$row = array();
				$row['ori_slot_id'] = $v[2];
                $arrUserSlotInfo = $this->getUserSlotInfo($row['ori_slot_id']);
                if($arrUserSlotInfo == false) {
                    continue;
                    //return false;
                }
                // get discount
                $arrMedia = $this->MediaManager->getMediaByAppId($arrUserSlotInfo['app_id']);
                if(!$arrMedia) {
                    $this->discount = 0.8;
                }
                $this->discount = intval($arrMedia[0]['proportion'])/100;

                $row['user_slot_id'] = $arrUserSlotInfo['slot_id'];
                $row['app_id'] = $arrUserSlotInfo['app_id'];
                $row['acct_id'] = $arrUserSlotInfo['account_id'];
				$row['ori_slot_name'] = $v[1];
				$row['ori_slot_type'] = $v[1];
				$row['pre_exposure_num'] = intval($v[3]);
				$row['post_exposure_num'] = intval($v[3]) * $this->discount;
                $row['pre_click_num'] = intval($v[4]);
                $row['post_click_num'] = intval($v[4]) * $this->discount;
				$row['pre_profit'] = floatval($v[5]);
				$row['post_profit'] = floatval($v[5]) * $this->discount;
				$row['click_rate'] = (intval($v[3]) == 0) ? 0 : round($v[4]/$v[3]*100, 3);
				$row['cpc'] = (intval($v[4]) == 0) ? 0 : round($v[5]/$v[4]*100, 3);
				$row['ecpm'] = (intval($v[3]) == 0) ? 0 : round($v[5]/$v[3]*1000, 3);
				$row['mark'] = 0;

                // re calc post_exposure_num & post_click_num
                $row['post_exposure_num'] = $row['ecpm'] == 0 ? 0 : intval($row['post_profit'] * 1000 / $row['ecpm']);
                $row['post_click_num'] = intval($row['post_exposure_num'] * $row['click_rate'] / 100);

                $row['date'] = $arrParams['date'];
                $row['create_time'] = time();
                $row['update_time'] = time();
				$sqlString = '('."'".implode( "','", $row ) . "'".')'; //批量
				$insertRows[] = $sqlString;
			}
            // 导入前先清空之前的数据 mark=0
            $this->Processes->delOriProfitDaily($arrParams);
			$result = $this->addDetail($insertRows, 'tab_slot_ori_profit_gdt_daily'); //批量将sql插入数据库。
            unset($insertRows);
            // 更新按钮状态
            if($result) {
                //todo user info
                $arrState['user'] = 1;//$this->arrUser['account_id'];
                $arrState['btn_load'] = 1;
                $arrState['btn_load_cancel'] = 0;
                $arrState['btn_sum'] = 0;
                $arrState['btn_sum_cancel'] = 0;
                $arrState['create_time'] = time();
                $arrState['update_time'] = time();
                $arrState['platform_en'] = $arrParams['source'];
                $arrState['btn_select'] = 1;
                $arrState['date'] = $arrParams['date'];
                $arrStateRet = $this->BtnState->insertBtnState($arrState);
            }

            return $result;
		}
	}//}}}//

    /**
     * processTUIAContent
     */
    private function processTUIAContent($arrContent, $arrParams) {//{{{//
        $chunkData = array_chunk($arrContent , 5000);
		$count = count($chunkData);
		for ($i = 0; $i < $count; $i++) {
			$insertRows = [];
			foreach($chunkData[$i] as $k => $value){
                if($k == 0) {
                    continue;
                }
				$string = mb_convert_encoding(trim(strip_tags($value)), 'utf-8', 'gbk');
				$v = explode(',', trim($string));
				$row = array();
				$row['ori_slot_id'] = $v[2];
                $arrUserSlotInfo = $this->getUserSlotInfo($row['ori_slot_id']);
                if($arrUserSlotInfo == false) {
                    continue;
                    //return false;
                }
                // get discount
                $arrMedia = $this->MediaManager->getMediaByAppId($arrUserSlotInfo['app_id']);
                if(!$arrMedia) {
                    $this->discount = 0.8;
                }
                $this->discount = intval($arrMedia[0]['proportion'])/100;

                $row['user_slot_id'] = $arrUserSlotInfo['slot_id'];
                $row['app_id'] = $arrUserSlotInfo['app_id'];
                $row['acct_id'] = $arrUserSlotInfo['account_id'];
				$row['ori_slot_name'] = $v[1];
				$row['ori_slot_type'] = $v[3];
				$row['pre_exposure_num'] = intval($v[4]);
				$row['post_exposure_num'] = intval($v[4]) * $this->discount;
                $row['pre_click_num'] = intval($v[5]);
                $row['post_click_num'] = intval($v[5]) * $this->discount;
				$row['pre_profit'] = floatval($v[7]);
				$row['post_profit'] = floatval($v[7]) * $this->discount;
				$row['click_rate'] = (intval($v[4]) == 0) ? 0 : round($v[5]/$v[4]*100, 3);
				$row['cpc'] = (intval($v[5]) == 0) ? 0 : round($v[7]/$v[5]*100, 3);
				$row['ecpm'] = (intval($v[4]) == 0) ? 0 : round($v[7]/$v[4]*1000, 3);
				$row['mark'] = 0;

                // re calc post_exposure_num & post_click_num
                $row['post_exposure_num'] = $row['ecpm'] == 0 ? 0 : intval($row['post_profit'] * 1000 / $row['ecpm']);
                $row['post_click_num'] = intval($row['post_exposure_num'] * $row['click_rate'] / 100);

                $row['date'] = $arrParams['date'];
                $row['create_time'] = time();
                $row['update_time'] = time();
				$sqlString = '('."'".implode( "','", $row ) . "'".')'; //批量
				$insertRows[] = $sqlString;
			}
            // 导入前先清空之前的数据 mark=0
            $this->Processes->delOriProfitDaily($arrParams);
			$result = $this->addDetail($insertRows, 'tab_slot_ori_profit_tuia_daily'); //批量将sql插入数据库。
            unset($insertRows);
            // 更新按钮状态
            if($result) {
                //todo user info
                $arrState['user'] = 1;//$this->arrUser['account_id'];
                $arrState['btn_load'] = 1;
                $arrState['btn_load_cancel'] = 0;
                $arrState['btn_sum'] = 0;
                $arrState['btn_sum_cancel'] = 0;
                $arrState['create_time'] = time();
                $arrState['update_time'] = time();
                $arrState['platform_en'] = $arrParams['source'];
                $arrState['btn_select'] = 1;
                $arrState['date'] = $arrParams['date'];
                $arrStateRet = $this->BtnState->insertBtnState($arrState);
            }

            return $result;
		}
	}//}}}//

    /**
     * processYEZIContent
     */
    private function processYEZIContent($arrContent, $arrParams) {//{{{//
        $chunkData = array_chunk($arrContent , 5000);
		$count = count($chunkData);
		for ($i = 0; $i < $count; $i++) {
			$insertRows = array();
			foreach($chunkData[$i] as $k => $value){
                if($k == 0) {
                    continue;
                }
				$string = mb_convert_encoding(trim(strip_tags($value)), 'utf-8', 'gbk');
				$v = explode(',', trim($string));
				$row = array();
				$row['ori_slot_id'] = $v[3];
                // TODO  多了一行广告位，我们没有对应关系，得continue，容错
                $arrUserSlotInfo = $this->getUserSlotInfo($row['ori_slot_id']);
                if($arrUserSlotInfo == false) {
                    continue;
                    //return false;
                }
                // get discount
                $arrMedia = $this->MediaManager->getMediaByAppId($arrUserSlotInfo['app_id']);
                if(!$arrMedia) {
                    $this->discount = 0.8;
                }

                $this->discount = intval($arrMedia[0]['proportion'])/100;
                $row['user_slot_id'] = $arrUserSlotInfo['slot_id'];
                $row['app_id'] = $arrUserSlotInfo['app_id'];
                $row['acct_id'] = $arrUserSlotInfo['account_id'];
				$row['ori_slot_name'] = $v[1];
				$row['ori_slot_type'] = $v[2];
				$row['pre_exposure_num'] = intval($v[8]);
				$row['post_exposure_num'] = intval($v[8]) * $this->discount;
                $row['pre_click_num'] = intval($v[9]);
                $row['post_click_num'] = intval($v[9]) * $this->discount;
				$row['pre_profit'] = floatval($v[10]);
				$row['post_profit'] = floatval($v[10]) * $this->discount;
				$row['click_rate'] = (intval($v[8]) == 0) ? 0 : round($v[9]/$v[8]*100, 3);
				$row['cpc'] = (intval($v[9]) == 0) ? 0 : round($v[10]/$v[9]*100, 3);
				$row['ecpm'] = (intval($v[8]) == 0) ? 0 : round($v[10]/$v[8]*1000, 3);

                // re calc post_exposure_num & post_click_num
                $row['post_exposure_num'] = $row['ecpm'] == 0 ? 0 : intval($row['post_profit'] * 1000 / $row['ecpm']);
                $row['post_click_num'] = intval($row['post_exposure_num'] * $row['click_rate'] / 100);

				$row['mark'] = 0;
                $row['date'] = $arrParams['date'];
                $row['create_time'] = time();
                $row['update_time'] = time();
				$sqlString = '('."'".implode( "','", $row ) . "'".')'; //批量
				$insertRows[] = $sqlString;
			}
            // 导入前先清空之前的数据 mark=0
            $this->Processes->delOriProfitDaily($arrParams);
			$result = $this->addDetail($insertRows, 'tab_slot_ori_profit_yezi_daily'); //批量将sql插入数据库。
            unset($insertRows);
            // 更新按钮状态
            if($result) {
                //todo user info
                $arrState['user'] = 1;//$this->arrUser['account_id'];
                $arrState['btn_load'] = 1;
                $arrState['btn_load_cancel'] = 0;
                $arrState['btn_sum'] = 0;
                $arrState['btn_sum_cancel'] = 0;
                $arrState['create_time'] = time();
                $arrState['update_time'] = time();
                $arrState['platform_en'] = $arrParams['source'];
                $arrState['btn_select'] = 1;
                $arrState['date'] = $arrParams['date'];

                $arrStateRet = $this->BtnState->insertBtnState($arrState);
            }

            return $result;
		}
	}//}}}//

	private function addDetail($rows, $table){//{{{//
        if(empty($rows)){
            return false;
        }
        //数据量较大,采取批量插入
        $strValues = implode(',', $rows);
        $sql = "INSERT IGNORE INTO $table(
            ori_slot_id,user_slot_id,app_id,acct_id,ori_slot_name,ori_slot_type,pre_exposure_num,
            post_exposure_num,pre_click_num,post_click_num,pre_profit,post_profit,click_rate,cpc,ecpm,
            mark,date,create_time,update_time) VALUES {$strValues}";
        $boolRes = $this->dbutil->query($sql);
        if(!$boolRes) {
            return false;
        }

        return $boolRes;
    }//}}}//

    private function getUserSlotInfo($strOriSlotId) {//{{{//
        $arrSelect = [
            'select' => '*',
            'where' => "upstream_slot_id='" . $strOriSlotId . "'",
        ];
        $arrRes = $this->dbutil->getAdslotmap($arrSelect);
        if(empty($arrRes[0])) {
            return false;
        }
        return $arrRes[0];
    }//}}}//

}
