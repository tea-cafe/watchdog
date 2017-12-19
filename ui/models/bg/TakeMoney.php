<?php
/**
 * 提现审核
 */ 
class TakeMoney extends CI_Model{
	public function __construct(){
		parent::__construct();
	}

	/*获取提现单信息*/
	public function getInfo($number){
		$where = array(
			'select' => '',
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

	/*修改提现订单信息，并更改状态*/
	public function modifyInfo($number,$params,$status,$remark){
		$where = array(
			'select' => 'info',
			'where' => 'number = '.$number.' AND status = 1',
		);
		$this->load->library('DbUtil');
		$info = $this->dbutil->getTmr($where);
		$ainfo = unserialize($info[0]['info']);
		$this->config->load('company_invoice_info');
		$companyInvoiceInfo = $this->config->item('invoice');

		$newInfo = array(
			'channel_info' => $info['media_info'],
			'company_invoice_info' => $companyInvoiceInfo,
			'channel_invoice_info' => $params,
		);

		$udp_where = array(
			'info' => serialize($newInfo),
			'status' => $status,
			'remark' => $remark,
			'where' => 'number = '.$number.' AND status = 1',
		);
		
		$res = $this->dbutil->udpTmr($udp_where);
		if($res['code'] == 0){
			return true;
		}else{
			return false;
		}
	}

}
?>
