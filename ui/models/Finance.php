<?php
	class Finance extends CI_Model{
		public function __construct(){
			parent::__construct();
            $this->load->library("DbUtil");
		}

        /**
         * 获取提现列表
         */
		public function getTakeMoneyList($accId,$startDate,$endDate,$pageSize,$currentPage){
			if(empty($status)){
				$statusStr = '';
			}else{
				$statusStr = ' AND status = "'.$status.'"';
			}

			if($currentPage == 1){
				$currentPage = 0;
			}else{
				$currentPage = ($currentPage - 1) * $pageSize;
            }
            
            /* 提现单查询 */
			$listWhere = array(
				'select' => 'id,time,account_id,number,money,status',
				'where' => 'time > '.$startDate.' AND time < '.$endDate.' AND account_id = "'.$accId.'"'.$statusStr,
				'order_by' => 'time desc',
				'limit' => $currentPage.','.$pageSize,
            );
			$tmrList = $this->dbutil->getTmr($listWhere);

            /* end */

            /* 分页信息查询 */
			$totalWhere = array(
				'select' => 'count(*)',
				'where' => 'time > '.$startDate.' AND time < '.$endDate.' AND account_id = "'.$accId.'"'.$statusStr,
			);

			$totalCount = $this->dbutil->getTmr($totalWhere);
            
            $paginAtion = array(
                'current' => empty($currentPage) ? '1':$currentPage,
                'pageSize' => $pageSize,
                'total' => empty($totalCount[0]['count(*)']) ? '0' : $totalCount[0]['count(*)'],
            );
            /* end */

            /* 账户余额查询 */
            $balanceWhere = array(
                'select' => 'money',
                'where' => 'account_id = "'.$accId.'" AND status = "1"',
            );
            
            $AccBalance = $this->dbutil->getAccBalance($balanceWhere);
            /* end */
            $AccBalance = empty($AccBalance[0]['money']) ? '0.00' : $AccBalance[0]['money'];

            /* 财务认证状态 */
            $financeWhere = array(
                'select' => 'check_status',
                'where' => 'account_id = "'.$accId.'"',
            );
            $strFinance = $this->dbutil->getAccount($financeWhere);
            /* end */
			$result['list'] = $tmrList;
            $result['balance'] = $AccBalance;
            $result['finance_status'] = $strFinance[0]['check_status'];
			$result['pagination'] = $paginAtion;
			return $result;
		}

        /**
         * 获取月账单列表
         */
		public function getMonthlyBill($accId,$pageSize,$currentPage){
            if($currentPage == 1){
				$currentPage = 0;
			}else{
				$currentPage = ($currentPage - 1) * $pageSize;
			}
			$listWhere = array(
				'select' => 'time,account_id,app_id,media_name,media_platform,money,status',
				'where' => 'account_id = "'.$accId.'"',
				'order_by' => 'time desc',
				'limit' => $currentPage.','.$pageSize,
            );
			$data = $this->dbutil->getMonthly($listWhere);

            foreach($data as $k => $v){
                $data[$k]['time'] = date('Y-m',$v['time']);
            }

			if(empty($data)){
				return $data;
			}	

			$totalWhere = array(
				'select' => 'count(*)',
				'where' => 'account_id = "'.$accId.'"',
			);

			$totalCount = $this->dbutil->getMonthly($totalWhere);

            $paginAtion = array(
                'current' => empty($currentPage) ? '1':$currentPage,
                'pageSize' => $pageSize,
                'total' => empty($totalCount[0]['count(*)']) ? '0' : $totalCount[0]['count(*)'],
            );
			$result['list'] = $data;
			$result['pagination'] = $paginAtion;
			
			return $result;
		}

		/* 获取提现单详情 */
		public function getTakeMoneyInfo($accId,$number){
			$where = array(
				'select' => '',
				'where' => 'account_id = "'.$accId.'" AND number = '.$number,
			);

			$data = $this->dbutil->getTmr($where);
			
			if(empty($data)){
				return $data;
			}

			$data[0]['bill_list'] = unserialize($data[0]['bill_list']);
			$data[0]['info'] = unserialize($data[0]['info']);

			return $data[0];
		}

		/*获取日账单列表*/
		public function getDailyBillList($appid,$startDate,$endDate,$limit){
			$listWhere = array(
				'select' => 'time,media_name,media_platform,money',
				'where' => 'time > '.$startDate.' AND time < '.$endDate.' AND app_id = "'.$appid.'"',
				'order_by' => 'time',
				'limit' => '0,'.$limit,
			);
            
			$data = $this->dbutil->getDaily($listWhere);

			if(empty($data)){
				return $data;
			}

			foreach($data as $key => $value){
				$data[$key]['time'] = date("Y-m-d",$value['time']);
			}
            
            $totalWhere = array(
				'select' => 'count(*)',
				'where' => 'time > '.$startDate.' AND time < '.$endDate.' AND app_id = "'.$appid.'"',
			);

			$totalCount = $this->dbutil->getDaily($totalWhere);
 
            $paginAtion = array(
                'current' => empty($currentPage) ? '1':$currentPage,
                'pageSize' => $limit,
                'total' => empty($totalCount[0]['count(*)']) ? '0' : $totalCount[0]['count(*)'],
            );
			$result['list'] = $data;
			$result['pagination'] = $paginAtion;
			
			return $result;
		}

		/**
		 * 检查账户财务状态
		 */
		public function checkFinanceInfo($accId){
			$where = array(
				'select' => 'check_status',
				'where' => 'account_id = "'.$accId.'"',
			);
            
            $status = $this->dbutil->getAccount($where);
			$status = $status[0]['check_status'];
			
			if($status === '2'){
				return $status;
			}else{
				return [];
			}
		}

		/**
		 * 查询账户余额
		 */
		public function getAccountMoney($accId){
			$where = array(
				'select' => 'status,money',
				'where' => 'account_id = "'.$accId.'"',
			);

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
		public function confirmTakeMoney($accId,$email,$money){
            /* 获取可以提现的月账单 */
            $billWhere = array(
				'select' => 'id,time,app_id,media_name,media_platform,money',
				'where' => 'account_id = "'.$accId.'" AND status = "0"',
            );
			$tmpList = $this->dbutil->getMonthly($billWhere);
			
			foreach($tmpList as $key => $value){
				$billList[$key]['id'] = $value['id'];
				$billList[$key]['time'] = date("Y-m",$value['time']);
				$billList[$key]['app_id'] = $value['app_id'];
				$billList[$key]['media_name'] = $value['media_name'];
				$billList[$key]['media_platform'] = $value['media_platform'];
				$billList[$key]['money'] = $value['money'];
            }

            /*获取媒体信息*/
			$infoWhere = array(
				'select' => 'account_company,bank,bank_branch,bank_account,email,contact_person,phone,contact_address,company,financial_object,account_holder',
				'where' => 'account_id = "'.$accId.'"',
			);
			$tmpInfo = $this->dbutil->getAccount($infoWhere);
            if($tmpInfo[0]['financial_object'] == 1){
                $tmpInfo[0]['account_company'] = $tmpInfo[0]['account_holder'];
                unset($tmpInfo[0]['account_holder']);
            }
			$tmpInfo[0]['money'] = $money;
			$info['channel_info'] = $tmpInfo[0];
            
            /* 获取开票信息 */
            $this->config->load('company_invoice_info');
            $info['company_info'] = $this->config->item('invoice')['info'];
            $info['mail'] = $this->config->item('invoice')['mail'];
            $info['invoice_info'] = array(
                'money' => '',
                'code' => '',
                'number' => '',
            );

			$params = array(
				0 => array(
					'type' => 'insert',
					'tabName' => 'tmr',
					'data' => array(
						'time' => time(),
						'account_id' => $accId,
						'number' => date("YmdHi").mt_rand(101,999),
						'money' => $money,
						'bill_list' => serialize($billList),
						'info' => serialize($info),
						'remark' => '',
						'status' => '0',
						'create_time' => time(),
						'update_time' => time(),
					),
				),
				1 => array(
					'type' => 'update',
					'tabName' => 'accbalance',
					'where' => 'account_id = "'.$accId.'"',
					'data' => array(
						'money' => 0,
						'update_time' => time(),
					),
				),
				2 => array(
					'type' => 'update',
					'tabName' => 'monthly',
				    'where' => 'account_id = "'.$accId.'" AND status = "0"',
					'data' => array(
						'status' => '1',
						'update_time' => time(),
					),
				),
			);

			$result['TmrStatus'] = $this->dbutil->sqlTrans($params);
            
            $TmrWhere = array(
                'select' => 'id,time,account_id,number,money,status',
                'where' => 'account_id = "'.$accId.'"',
                'order_by' => 'time desc',
                'limit' => '0,20',
            );


            /* 分页信息查询 */
			$totalWhere = array(
				'select' => 'count(*)',
                'where' => 'account_id = "'.$accId.'"',
			);

			$totalCount = $this->dbutil->getTmr($totalWhere);
            
            $paginAtion = array(
                'current' => '1',
                'pageSize' => '20',
                'total' => empty($totalCount[0]['count(*)']) ? '0' : $totalCount[0]['count(*)'],
            );
            /* end */

            $result['data']['list'] = $this->dbutil->getTmr($TmrWhere);
            $result['data']['balance'] = '0.00';
            $result['data']['finance_status'] = '2';
			$result['data']['pagination'] = $paginAtion;

            return $result;
		}
	}
?>	
