<?php
/*使用AccountID当数组key，抑制Notice错误*/
error_reporting(E_ALL & ~E_NOTICE);
/**
 * 渠道信息列表
 */
class ChargingManager extends CI_Model{
	public function __construct(){
		parent::__construct();
		$this->load->library('DbUtil');
	}

	/*获取渠道列表*/
	public function getChannelList($arrParams){
		if($arrParams['channelName'] == 'all'){
			$sqlWhere = '';
		}else{
			$sqlWhere = 'company like "%'.$arrParams['channelName'].'%" OR contact_person like "%'.$arrParams['channelName'].'%"';
		}
		
		$totalWhere = array(
			'select' => 'count(*)',
			'where' => $sqlWhere,
		);
		
		$where = array(
			'select' => 'account_id,company,contact_person,phone,email,create_time,charging_status',
			'where' => $sqlWhere,
			'order_by' => 'id ASC',
			'limit' => ($arrParams['current']-1) * $arrParams['pageSize'].','.$arrParams['pageSize'],
		);

		if($sqlWhere == ''){
			unset($totalWhere['where']);
			unset($where['where']);
		}
		$total = $this->dbutil->getCharging($totalWhere);

		if($total[0]['count(*)'] == '0'){
			return [];
		}

		$res = $this->dbutil->getCharging($where);

		$data = array();
		foreach($res as $k => $v){
			$data[$k]['account_id'] = $v['account_id'];
			$data[$k]['email'] = $v['email'];
			$data[$k]['charging_status'] = $v['charging_status'];
			$data[$k]['phone'] = $v['phone'];
			$data[$k]['contact_person'] = $v['contact_person'];
			$data[$k]['company'] = empty($v['company']) ? $v['contact_person'] : $v['company'];
			$data[$k]['create_time'] = $v['create_time'];
		}
		
		return [
			'list' => $data,
			'pagination' => [
				'total' => (int)$total[0]['count(*)'],
				'pageSize' => (int)$arrParams['pageSize'],
				'current' => (int)$arrParams['current'],
			],
		];
	}

	public function getChannelInfo($params){
		$where = array(
			'select' => '*',
			'where' => 'account_id="'.$params.'"',
		);

		$res = $this->dbutil->getCharging($where);
		
		if(!$res){
			return [];
		}

		$listWhere = array(
			'select' => 'charging_name',
			'where' => 'account_id="'.$params.'"',
		);

		$nameList = $this->dbutil->getChargingList($listWhere);
		$chargingList = '';
		if(!empty($nameList)){
			foreach($nameList as $key=>$value){
				$chargingList .= $value['charging_name'].',';
			}
			$chargingList = substr($chargingList, 0, -1);	
		}
		$res[0]['charging_name'] = $chargingList;
		
		return $res;
	}

	public function ChannelCheck($arrParams){
		$where = array(
			'charging_status' => $arrParams['check_status'],
			'where' => 'account_id="'.$arrParams['account_id'].'" AND charging_status="1"',
		);

		$res = $this->dbutil->udpCharging($where);
		if($res['code'] == '0'){
			return true;
		}

		return false;
	}

	public function ChargingAdd($arrParams){
		$statusWhere = array(
			'select' => 'charging_status',
			'where' => 'account_id="'.$arrParams['account_id'].'"',
		);

		$status = $this->dbutil->getCharging($statusWhere);
		if($status[0]['charging_status'] != '2'){
			return false;
		}
		
		$where = array(
			'account_id' => $arrParams['account_id'],
			'charging_name' => $arrParams['charging_name'],
			'create_time' => time(),
			'update_time' => time(),
		);

		$res = $this->dbutil->setChargingList($where);
		if($res['code'] == '0'){
			return true;
		}

		return false;
	}

	public function getDailyData($arrParams){
		$totalWhere = array(
			'select' => 'count(*)',
			'where' => 'date="'.$arrParams['date'].'"',
		);
		$total = $this->dbutil->getChargingDataDaily($totalWhere);

		if(!$total[0]['count(*)']){
			return [
				'list' => [],
				'pagination' => [
					'total' => 0,
					'current' => (int)$arrParams['current'],
					'pageSize' => (int)$arrParams['pageSize'],
				],
			];
		}

		$where = array(
			'select' => '*',
			'where' => 'date="'.$arrParams['date'].'"',
			'order_by' => 'id ASC',
			'limit' => ($arrParams['current']-1) * $arrParams['pageSize'].','.$arrParams['pageSize'],
		);

		$res = $this->dbutil->getChargingDataDaily($where);
		foreach($res as $key => $value){
			$mark[$value['mark']] = $value['mark'];
		}

		return [
			'list' => $res,
			'mark' => reset($mark),
			'pagination' => [
				'total' => (int)$total[0]['count(*)'],
				'current' => (int)$arrParams['current'],
				'PageSize' => (int)$arrParams['pageSize'],
				'date' => $arrParams['date'],
			],
		];
	}

	public function getReportData($arrParams){
		$queryWhere = '';
		$level = 'channel';
		if(isset($arrParams['account_id'])){
			$queryWhere = 'account_id="'.$arrParams['account_id'].'" AND ';
			$level = 'charging';
		}

		if(isset($arrParams['charging_name'])){
			$queryWhere = 'charging_name="'.$arrParams['charging_name'].'" AND ';
			$level = 'days';
		}

		$totalWhere = array(
			'select' => 'account_id',
			'where' => $queryWhere."DATE_FORMAT(date,'%Y-%m-%d') >= DATE_FORMAT('".$arrParams['startDate']."','%Y-%m-%d') AND DATE_FORMAT(date,'%Y-%m-%d') <= DATE_FORMAT('".$arrParams['endDate']."','%Y-%m-%d')",
		);

		if($level == 'channel'){
			$totalWhere['select'] = 'account_id';
		}elseif($level == 'charging'){
			$totalWhere['select'] = 'charging_name';
		}elseif($level == 'days'){
			$totalWhere['select'] = 'date';
		}

		$total = $this->dbutil->getChargingDataDaily($totalWhere); 
		foreach($total as $key => $value){
			$index = key($value);
			$tmpTotal[$value[$index]] = $value[$index];
		}

		if(empty($total)){
			return [
				'list' => '',
				'pagination' => [
					'total' => 0,
					'current' => (int)$arrParams['current'],
					'PageSize' => (int)$arrParams['pageSize'],
				],
			];
		}

		$where = array(
			'select' => 'account_id,charging_name,search_num,click_num,money,date',
			'where' => $queryWhere."DATE_FORMAT(date,'%Y-%m-%d') >= DATE_FORMAT('".$arrParams['startDate']."','%Y-%m-%d') AND DATE_FORMAT(date,'%Y-%m-%d') <= DATE_FORMAT('".$arrParams['endDate']."','%Y-%m-%d')",
		);

		$res = $this->dbutil->getChargingDataDaily($where);

		if(empty($res)){
			return [
				'list' => '',
				'pagination' => [
					'total' => 0,
					'current' => (int)$arrParams['current'],
					'PageSize' => (int)$arrParams['pageSize'],
				],
			];
		}

		$res = $this->handleData($level,$res);

		if($arrParams['current'] == 1){
			$list = array_slice($res,0,$arrParams['pageSize']);
		}else{
			$startKey = ($arrParams['current'] - 1) * $arrParams['pageSize'];
			$list = array_slice($res,$startKey,$arrParams['pageSize']);
		}

		return [
			'list' => $list,
			'pagination' => [
				'total' => (int)count($res),
				'current' => (int)$arrParams['current'],
				'PageSize' => (int)$arrParams['pageSize'],
				'startDate' => $arrParams['startDate'],
				'endDate' => $arrParams['endDate'],
			],
		];
	}

	public function handleData($level,$data){
		switch($level){
			case "channel":
				$res = array();
				foreach($data as $key => $value){
					$accId = $value['account_id'];
					$res[$accId]['account_id'] = $accId;
					$res[$accId]['search_num'] += $value['search_num'];
					$res[$accId]['click_num'] += $value['click_num'];
					$res[$accId]['money'] += $value['money'];
				}

				foreach($res as $key => $value){
					$res[$key]['channelName'] = $this->getChannelInfo($key)[0]['company'];
					$res[$key]['click_rate'] = (intval($value['search_num']) == 0) ? 0 : round($value['click_num']/$value['search_num']*100, 3);;
				}
				$res = array_values($res);
				break;
			case "charging":
				$res = array();
				foreach($data as $key => $value){
					$chargingName = $value['charging_name'];
					$res[$chargingName]['charging_name'] = $chargingName;
					$res[$chargingName]['account_id'] = $value['account_id'];
					$res[$chargingName]['search_num'] += $value['search_num'];
					$res[$chargingName]['click_num'] += $value['click_num'];
					$res[$chargingName]['money'] += $value['money'];
				}
				
				foreach($res as $key => $value){
					$res[$key]['channelName'] = $this->getChannelInfo($value['account_id'])[0]['company'];
					$res[$key]['click_rate'] = (intval($value['search_num']) == 0) ? 0 : round($value['click_num']/$value['search_num']*100, 3);
				}
				$res = array_values($res);
				break;
			case "days":
				foreach($data as $key => $value){
					$data[$key]['click_rate'] = (intval($value['search_num']) == 0) ? 0 : round($value['click_num']/$value['search_num']*100, 3);
					$data[$key]['channelName'] = $this->getChannelInfo($value['account_id'])[0]['company'];
				}
				$res = $data;
				break;
		}

		return $res;
	}

	function modifyImportDataMark($arrParams){
		if($arrParams['mark'] == 1){
			$where = array(
				'mark' => '1',
				'where' => 'date="'.$arrParams['date'].'" AND mark="0"',
			);

			$res = $this->dbutil->udpChargingDataDaily($where);
		}elseif($arrParams['mark'] == 2){
			$where = array(
				'where' => 'date="'.$arrParams['date'].'" AND mark="1"',
			);
			$res = $this->dbutil->delChargingDataDaily($where);
		}

		if($res['code'] == 0){
			return true;
		}

		return false;
	}
}

?>
