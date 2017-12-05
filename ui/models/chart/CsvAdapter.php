<?php
class CsvAdapter extends CI_Model {
	public function __construct() {
		parent::__construct();
		$this->load->library('CsvReader');
        $this->discount = 0.8;
	}

	public function baidu() {
		$ret = $this->csvreader->import();
		$arrContent = $this->csvreader->read_file();
		if($ret == true) {
			$arrContent = $this->csvreader->read_file();
            $this->process_content($arrContent);
		}
        return [];
	}

    private function process_content($arrContent) {
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
                //查找用户slot_id信息 TODO
                $arrUserSlotInfo = $this->getUserSlotInfo($row['ori_slot_id']);

				$row['ori_slot_name'] = $v[1];
				$row['ori_slot_type'] = $v[2];
				$row['pre_exposure_num'] = intval($v[3]);
				$row['post_exposure_num'] = intval($v[3]) * $this->discount;
                $row['pre_click_num'] = intval($v[4]);
                $row['post_click_num'] = intval($v[4]) * $this->discount;
				$row['pre_profit'] = floatval($v[5]);
				$row['post_profit'] = floatval($v[5]) * $this->discount;
				$row['click_rate'] = (intval($v[3]) == 0) ? 0 : round($v[4]/$v[3], 3);
				$row['cpc'] = (intval($v[4]) == 0) ? 0 : round($v[5]/$v[4], 3);
				$row['ecpm'] = (intval($v[3]) == 0) ? 0 : round($v[5]/($v[3]*1000), 3);
				$row['mark'] = 0;
                $row['date'] = date('Y-m-d');
				$sqlString = '('."'".implode( "','", $row ) . "'".')'; //批量
				$insertRows[] = $sqlString;
			}
            var_dump($insertRows);exit;
			$result = $this->addDetail($insertRows); //批量将sql插入数据库。
		}
	}

	private function addDetail($rows){  
        if(empty($rows)){
            return false;
        }
        //数据量较大,采取批量插入  
        $data = implode(',', $rows);  
        $sql = "INSERT IGNORE INTO tb_account_detail(cdate,business,project,shopname,shopid,fanli,fb,jifen) VALUES {$data}";
        echo $sql;exit;
        $result = $this->query($sql);
        return true;
    } 

    private function getUserSlotInfo($strOriSlotId) {
    }
}
