<?php
/**
 * 渠道信息列表
 */
class ChannelManager extends CI_Model{
	public function __construct(){
		parent::__construct();
	}

	/*获取渠道列表*/
	public function getList($keyWord,$pageSize,$currentPage,$status){
        if($currentPage == 1){
            $currentPage = 0;
        }else{
            $currentPage = ($currentPage - 1) * $pageSize;
        }

		if(empty($keyWord)){
			$StrKeyWord = '';
		}else{
			$StrKeyWord = 'company like "%'.$keyWord.'%" OR contact_person like "%'.$keyWord.'%"';
		}
		if(empty($status)){
			$StrStatus = '';
		}else{
			$StrStatus = ' AND check_status = '.$status;
		}
		
		$listWhere = array(
			'select' => 'account_id,company,contact_person,financial_object,phone,email,create_time,check_status',
			'where' => $StrKeyWord.$StrStatus,
            'limit' => empty($pageSize) || empty($currentPage) ? '0,20' : $currentPage.','.$pageSize,
        );

        if(empty($listWhere['where'])){
            unset($listWhere['where']);
        }

        $this->load->library('DbUtil');
		$res = $this->dbutil->getAccount($listWhere);
        
        if(empty($res)){
			return [];
		}

		foreach($res as $k => $v){
			$data[$k]['account_id'] = $v['account_id'];
			$data[$k]['type'] = $v['financial_object'];
			$data[$k]['email'] = $v['email'];
			$data[$k]['status'] = $v['check_status'];
			$data[$k]['phone'] = $v['phone'];
			$data[$k]['contact_person'] = $v['contact_person'];
			$data[$k]['company'] = empty($v['company']) ? $v['contact_person'] : $v['company'];
			$data[$k]['create_time'] = date("Y-m-d H:i:s",$v['create_time']);
		}

		$totalWhere = array(
			'select' => 'count(*)',
			'where' => $StrKeyWord.$StrStatus,
        );
        if(empty($totalWhere['where'])){
            unset($totalWhere['where']);
        }
		$totalCount = $this->dbutil->getAccount($totalWhere);

        $paginAtion = array(
            'current' => empty($currentPage) ? '1':$currentPage,
            'pageSize' => $pageSize,
            'total' => $totalCount[0]['count(*)'],
        );
        $result['list'] = $data;
        $result['pagination'] = $paginAtion;
        
		return $result;
	}

	/*获取渠道信息*/
	public function getInfo($account_id){
		$where = array(
			'select' => '',
			'where' => 'account_id = '.$account_id,
			'order_by' => '',
			'limit' => '',
		);

		$this->load->library('DbUtil');
		$res = $this->dbutil->getAccount($where);
		unset($res[0]['passwd']);
		
		if(empty($res)){
			return [];
		}

		return $res[0];
	}

	/*修改财务认证*/
	public function modifyFinanceStatus($email,$status,$remark){
		$where = array(
			'check_status' => $status,
			'remark' => $remark,
			'where' => 'email = "'.$email.'"',
		);	
		
		$this->load->library('DbUtil');
		$res = $this->dbutil->udpAccount($where);
		
		if($res['code'] == 0){
			return true;
		}else{
			return false;
		}
	}
}

?>
