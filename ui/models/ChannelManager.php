<?php
/**
 * 渠道信息列表
 */
class ChannelManager extends CI_Model{
	public function __construct(){
		parent::__construct();
		$this->load->library('DbUtil');
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
			'select' => 'account_id,company,contact_person,phone,email,create_time,check_status',
			'where' => $StrKeyWord.$StrStatus,
            'order_by' => 'id desc',
            'limit' => $currentPage.','.$pageSize,
        );

        if(empty($listWhere['where'])){
            unset($listWhere['where']);
        }

		$res = $this->dbutil->getAccount($listWhere);
        
        if(empty($res)){
			return [];
		}

		foreach($res as $k => $v){
			$data[$k]['account_id'] = $v['account_id'];
			$data[$k]['email'] = $v['email'];
			$data[$k]['check_status'] = $v['check_status'];
			$data[$k]['phone'] = $v['phone'];
			$data[$k]['contact_person'] = $v['contact_person'];
			$data[$k]['company'] = empty($v['company']) ? $v['contact_person'] : $v['company'];
			$data[$k]['create_time'] = $v['create_time'];
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
            'total' => empty($totalCount[0]['count(*)']) ? '0' : $totalCount[0]['count(*)'],
        );
        $result['list'] = $data;
        $result['pagination'] = $paginAtion;
        
		return $result;
	}

	/*获取渠道信息*/
	public function getInfo($accId){
        $where = array(
			'select' => '*',
			'where' => 'account_id = "'.$accId.'"',
		);

        $res = $this->dbutil->getAccount($where);
		if(empty($res)){
			return [];
        }

		unset($res[0]['passwd']);
		return $res[0];
	}

	/*修改财务认证*/
	public function modifyFinanceStatus($accId,$status,$remark){
        $statusWhere = array(
            'select' => 'check_status',
            'where' => 'account_id = "'.$accId.'"',
        );

        $statusArr = $this->dbutil->getAccount($statusWhere);

        if($statusArr[0]['check_status'] == '0'){
            return '2';
        }

        $where = array(
			'check_status' => $status,
			'auth_finance_remark' => $remark,
			'where' => 'account_id = "'.$accId.'" AND check_status = 1',
		);	
        
        if(empty($where['remark'])){
            unset($where['remark']);
        }
		$res = $this->dbutil->udpAccount($where);
        
        if($res['code'] == 0){
			return true;
		}else{
			return false;
		}
	}

    /**
     * getAcctByAcctId
     */
    public function getAcctByAcctId($AccId) {
        $where = array(
			'select' => 'company',
			'where' => 'account_id = "'.$AccId.'"',
		);

		$res = $this->dbutil->getAccount($where);
		
		if(empty($res)){
			return false;
		}

		return $res[0];
    }
}

?>
