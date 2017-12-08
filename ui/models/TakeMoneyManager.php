<?php
/**
 * 提现审核
 */ 
class TakeMoneyManager extends CI_Model{
	public function __construct(){
		parent::__construct();
	}

	/*获取提现单信息*/
	public function getInfo($number){
		$where = array(
			'select' => 'time,account_id,number,money,bill_list,info,status,remark',
			'where' => 'number = '.$number,
		);

		$this->load->library('DbUtil');
		$res = $this->dbutil->getTmr($where);
		if(empty($res)){
			return [];
		}
	
		$res[0]['bill_list'] = unserialize($res[0]['bill_list']);
		$res[0]['info'] = unserialize($res[0]['info']);
		return $res;
	}

	public function modifyInfo($orderNumber,$params,$status,$remark){
		/*获取账单用户唯一标识：account_id*/
		$accWhere = array(
			'select' => 'account_id',
			'where' => 'number = '.$orderNumber,
		);
		$this->load->library('DbUtil');
		$accRes = $this->dbutil->getTmr($accWhere);
		$account_id = $accRes[0]['account_id'];

		/* 查询提现单信息 */
		$infoWhere = array(
			'select' => 'info',
			'where' => 'number = '.$orderNumber.' AND status = 1',
		);
		$this->load->library('DbUtil');
		$info = $this->dbutil->getTmr($infoWhere);
		$info = unserialize($info[0]['info']);
		$this->config->load('company_invoice_info');
		$companyInvoiceInfo = $this->config->item('invoice');
		$newInfo = array(
			'channel_info' => $info['channel_info'],
			'company_invoice_info' => $companyInvoiceInfo,
			'channel_invoice_info' => $params,
		);


		switch($status){
			case '2':
				/* 审核通过操作 */
				$Res = $this->adopt($account_id,$orderNumber,$newInfo);
				break;
			case '3':
				/* 审核失败操作 */
				$Res = $this->reject($account_id,$orderNumber,$newInfo,$remark);
				break;
		}

		return $Res;
	}

	/**
	 * 审核通过
	 * 修改提现单状态,月账单状态
	 */	
	private function adopt($accId,$orderNumber,$params){
		$udpWhere = array(
			0 => array(
				'type' => 'update',
				'tabName' => 'tmr',
				'where' => 'number = '.$orderNumber.' AND status = 1',
				'data' => array(
					'info' => serialize($params),
					'status' => '2',
					'update_time' => time(),
				),
			),
			1 => array(
				'type' => 'update',
				'tabName' => 'monthly',
				'where' => 'account_id = "'.$accId.'" AND status = 2',
				'data' => array(
					'status' => '3',
					'update_time' => time(),
				),
			),
		);
		$this->load->library('DbUtil');
		$res = $this->dbutil->sqlTrans($udpWhere);
		
		return $res;
	}
	
	/**
	 * 审核失败
	 * 回滚账户余额、月账单余额和状态
	 */
	private function reject($accId,$orderNumber,$params,$remark){
		$udpWhere = array(
			0 => array(
				'type' => 'update',
				'tabName' => 'tmr',
				'where' => 'number = '.$orderNumber.' AND status = 1',
				'data' => array(
					'info' => serialize($params),
					'status' => '3',
					'remark' => $remark,
					'update_time' => time(),
				),
			),
			1 => array(
				'type' => 'update',
				'tabName' => 'accbalance',
				'where' => 'account_id = "'.$accId.'"',
				'data' => array(
					'money' => $params['channel_info']['money'],
					'update_time' => time(),
				),
			),
			2 => array(
				'type' => 'update',
				'tabName' => 'monthly',
				'where' => 'account_id = "'.$accId.'" AND status = 2',
				'data' => array(
					'status' => '1',
					'update_time' => time(),
				),
			),
		);

		$result = $this->dbutil->sqlTrans($udpWhere);

		return $result;
	}

	/**
	 * 已打款
	 */
	public function remitMoney($orderNumber){
		$udpWhere = array(
			'status' => '4',
			'update_time' => time(),
			'where' => 'number = '.$orderNumber.' AND status = 2'
		);

		$this->load->library('DbUtil');
		$result = $this->dbutil->udpTmr($udpWhere);
		
		if($result['code'] == 0){
			return true;
		}else{
			return false;
		}
	}

}
?>
