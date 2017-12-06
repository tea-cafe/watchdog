<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 后台 渠道管理
 */

class Channel extends BG_Controller {
	public function __construct(){
		parent::__construct();
	}

	/*渠道列表*/
	public function index(){
		$pageSize = $this->input->get('pagesize',true);
		$currentPage = $this->input->get('currentpage',true);
		$keyWord = $this->input->get('keyword',true);
		$status = $this->input->get('status',true);

		if(empty($pageSize) || empty($currentPage)){
			$pageSize = '';
			$currentPage = '';
		}
		$this->load->model('bg/Channel');
		$res = $this->Channel->getList($keyWord,$pageSize,$currentPage,$status);
		
		if(empty($res)){
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'渠道列表查询失败');
		}

		return $this->outJson($res,ErrCode::OK,'渠道列表查询成功');
	}

	/*渠道信息详情*/
	public function content(){
		$account_id = $this->input->get('accountid',true);
		if(empty($account_id)){
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'参数有误');
		}
		$this->load->model('bg/Channel');
		$res = $this->Channel->getInfo($account_id);
		
		if(empty($res)){
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'参数错误');
		}

		return $this->outJson($res,ErrCode::OK,'获取数据成功');
	}

	/*财务认证*/
	public function authFinance(){
		$account_id = $this->input->post('accountid',true);
		$status = $this->input->post('status',true);
		$remark = $this->input->post('remark',true);
		
		if(empty($account_id)){
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'参数有误');
		}

		if($status == '3' && empty($remark)){
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'未填写审核失败原因');
		
		}

		$this->load->model('UserManager');
		$email = $this->UserManager->checkLogin();
		if(empty($account)){
			$email = '2494591314@qq.com';
		}else{
			$email = $account['email'];
		}

		$this->load->model('bg/Channel');
		$res = $this->Channel->modifyFinanceStatus($account_id,$email,$status,$remark);
		if($res){
			return $this->outJson('',ErrCode::OK,'修改成功');
		}else{
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'参数有误');
		}
	}
}
?>
