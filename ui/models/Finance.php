<?php
	class Finance extends CI_Model{
		public function __construct(){
			parent::__construct();
		}
	
		/*获取提现列表*/
		public function getTakeMoneyList($account,$startDate,$endDate,$pageSize,$currentPage,$status){
			if(empty($status)){
				$statusStr = '';
			}else{
				$statusStr = ' AND status = '.$status;
			}

			if($currentPage == 1){
				$currentPage = 0;
			}else{
				$currentPage = $currentPage * $pageSize;
			}

			//var_dump($status);
			$where = array(
				'select' => '',
				'where' => 'time > '.$startDate.' AND time < '.$endDate.' AND account = "'.$account.'"'.$statusStr,
				'order_by' => 'time',
				'limit' => empty($pageSize) || empty($currentPage) ? '0,20' : $currentPage.','.$pageSize,
			);
			$this->load->library("DbUtil");
			$data = $this->dbutil->getTmr($where);
			
			if(empty($data)){
				return $data;
			}	
			
			foreach($data as $key => $value){
				$data[$key]['time'] = date("Y-m-d H:i:s",$value['time']);
				$data[$key]['media_list'] = unserialize($value['media_list']);
				$data[$key]['info'] = unserialize($value['info']);
			}

			$total_where = array(
				'select' => 'count(*)',
				'where' => 'time > '.$startDate.' AND time < '.$endDate.' AND account = "'.$account.'"'.$statusStr,
				'order_by' => '',
				'limit' => '',
			);

			$totalCount = $this->dbutil->getTmr($total_where);
			$totalCount = $totalCount[0]['count(*)'];
			$totalPage = ceil($totalCount / $pageSize);

			$result['list'] = $data;
			$result['totalCount'] = $totalCount;
			$result['totalPage'] = $totalPage;
			$result['list'] = $data;
			return $result;
		}

		/*获取月账单列表*/
		public function getMonthlyBill($account,$pageSize,$currentPage){
			if($currentPage == 1){
				$currentPage = 0;
			}else{
				$currentPage = $currentPage * $pageSize;
			}
			
			$where = array(
				'select' => '',
				'where' => 'account = "'.$account.'"',
				'order_by' => 'time',
				'limit' => empty($pageSize) || empty($currentPage) ? '0,20' : $currentPage.','.$pageSize,
			);
			$this->load->library("DbUtil");
			$data = $this->dbutil->getMonthly($where);

			if(empty($data)){
				return $data;
			}	

			$total_where = array(
				'select' => 'count(*)',
				'where' => 'account = "'.$account.'"',
				'order_by' => '',
				'limit' => '',
			);

			$totalCount = $this->dbutil->getMonthly($total_where);
			$totalCount = $totalCount[0]['count(*)'];
			$totalPage = ceil($totalCount / $pageSize);

			$result['list'] = $data;
			$result['totalCount'] = $totalCount;
			$result['totalPage'] = $totalPage;
			$result['list'] = $data;
			
			return $result;
		}

		/* 获取提现单详情 */
		public function getTakeMoneyInfo($account,$number){
			$where = array(
				'select' => '',
				'where' => 'account = "'.$account.'" AND number = '.$number,
				'order_by' => '',
				'limit' => '',
			);

			$this->load->library("DbUtil");
			$data = $this->dbutil->getTmr($where);
			
			if(empty($data)){
				return $data;
			}

			$data[0]['media_list'] = unserialize($data[0]['media_list']);
			$data[0]['info'] = unserialize($data[0]['info']);

			return $data[0];
		}

		/*获取日账单列表*/
		public function getDailyBillList($appid,$startDate,$endDate,$limit){
			$where = array(
				'select' => '',
				'where' => 'time > '.$startDate.' AND time < '.$endDate.' AND appid = '.$appid,
				'order_by' => 'time',
				'limit' => '0,'.$limit,
			);
			
			$this->load->library("DbUtil");
			$data = $this->dbutil->getDaily($where);
			
			if(empty($data)){
				return $data;
			}

			foreach($data as $key => $value){
				$data[$key]['time'] = date("Y-m-d",$value['time']);
			}
			
			$total_where = array(
				'select' => 'count(*)',
				'where' => 'time > '.$startDate.' AND time < '.$endDate.' AND appid = '.$appid,
				'order_by' => '',
				'limit' => '',
			);

			$totalCount = $this->dbutil->getDaily($total_where);
			$totalCount = $totalCount[0]['count(*)'];
			$totalPage = ceil($totalCount / 20);

			$result['list'] = $data;
			$result['totalCount'] = $totalCount;
			$result['totalPage'] = $totalPage;
			$result['list'] = $data;
			
			return $result;
		}

		/**
		 * 检查账户财务状态
		 */
		public function checkFinanceInfo($account){
			$where = array(
				'select' => 'check_status',
				'where' => 'email = "'.$account.'"',
				'order_by' => '',
				'limit' => '',
			);
			$this->load->library('DbUtil');
			$status = $this->dbutil->getAccount($where);
			$status = $status[0]['check_status'];
			
			if($status === '2'){
				return true;
			}else{
				return false;
			}
		}

		/**
		 * 查询账户余额
		 */
		public function getAccountMoney($email){
			$where = array(
				'select' => 'status,money',
				'where' => 'account = "'.$email.'"',
				'order_by' => '',
				'limit' => '',
			);

			$this->load->library('DbUtil');
			$result = $this->dbutil->getAccBalance($where);
			
			if(empty($result)){
				return [];
			}

			$data['money'] = floatval($result[0]['money']);
			$data['status'] = (int)$result[0]['status'];
			
			return $data;
		}

		/**
		 * 提现操作
		 */
		public function confirmTakeMoney($account,$money){
			/*获取媒体提现的月账单*/
			$startDate = 1490976000;
			$endDate = time();
			$mediaWhere = array(
				'select' => 'time,appid,media_name,media_platform,money',
				'where' => 'time > '.$startDate.' AND time <'.$endDate.' AND account = "'.$account.'"',
				'order_by' => 'time',
				'limit' => '',
			);
			$this->load->library('DbUtil');
			$tmpList = $this->dbutil->getMonthly($mediaWhere);
			
			foreach($tmpList as $key => $value){
				$MediaList[$key]['time'] = date("Y-m",$value['time']);
				$MediaList[$key]['appid'] = $value['appid'];
				$MediaList[$key]['media_name'] = $value['media_name'];
				$MediaList[$key]['media_platform'] = $value['media_platform'];
				$MediaList[$key]['money'] = $value['money'];
			}

			/*获取媒体信息*/
			$infoWhere = array(
				'select' => 'email,contact_person,phone,bank_account,company_address,company,bank,bank_branch',
				'where' => 'email = "'.$account.'"',
				'order_by' => '',
				'limit' => '',
			);
			$tmpInfo = $this->dbutil->getAccount($infoWhere);
			$tmpInfo[0]['media'] = $MediaList[0]['media_name'].'等';
			$tmpInfo[0]['money'] = $money;
			$info['media_info'] = $tmpInfo[0];

			$invoiceInfo = array(
				'company' => '杭州推啊网络科技有限公司',
				'company_address' => '杭州市西湖区文一西路98号数娱大厦808室',
				'phone' => '0571-28258680',
			);

			$info['invoice_info'] = $invoiceInfo;
			
			$params = array(
				0 => array(
					'type' => 'insert',
					'tabName' => 'tmr',
					'data' => array(
						'time' => time(),
						'account' => $account,
						'number' => date("YmdHi").mt_rand(101,999),
						'money' => $money,
						'media_list' => serialize($MediaList),
						'info' => serialize($info),
						'note' => '',
						'status' => '1',
						'create_time' => time(),
						'update_time' => time(),
					),
				),
				1 => array(
					'type' => 'update',
					'tabName' => 'accbalance',
					'where' => 'account = "'.$account.'"',
					'data' => array(
						'money' => 0,
						'create_time' => time(),
						'update_time' => time(),
					),
				),
				2 => array(
					'type' => 'update',
					'tabName' => 'monthly',
					'where' => 'time > '.$startDate.' AND time <'.$endDate.' AND account = "'.$account.'"',
					'data' => array(
						'status' => '2',
					),
				),
			);
			
			$result = $this->dbutil->sqlTrans($params);
			
			return $result;
		}
	}
?>	
