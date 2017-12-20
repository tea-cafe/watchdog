<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 后台 渠道(用户)管理
 */

class Channel extends BG_Controller {
	public function __construct(){
		parent::__construct();
        $this->load->model('ChannelManager');
	}

	/*渠道列表 搜索*/
	public function index(){
        if(empty($this->arrUser)){
            return $this->outJson('',ErrCode::ERR_NOT_LOGIN);
        }

        $pageSize = $this->input->get('pagesize',true);
		$currentPage = $this->input->get('currentpage',true);
		$keyWord = $this->input->get('channel_name',true);
		$status = $this->input->get('status',true);

		if(empty($pageSize) || empty($currentPage)){
			$pageSize = 20;
			$currentPage = 1;
		}
		$res = $this->ChannelManager->getList($keyWord,$pageSize,$currentPage,$status);
		
		if(empty($res)){
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'渠道列表查询失败');
		}

		return $this->outJson($res,ErrCode::OK,'渠道列表查询成功');
	}

	/*渠道信息详情*/
	public function content(){
        if(empty($this->arrUser)){
            return $this->outJson('',ErrCode::ERR_NOT_LOGIN);
        }

        $account_id = $this->input->get('account_id',true);
		if(empty($account_id)){
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'参数有误');
		}
        
		$res = $this->ChannelManager->getInfo($account_id);
		
		if(empty($res)){
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'参数错误');
		}

		return $this->outJson($res,ErrCode::OK,'获取数据成功');
	}

	/*财务认证*/
	public function authFinance(){
        if(empty($this->arrUser)){
            return $this->outJson('',ErrCode::ERR_NOT_LOGIN);
        }

        $accId = $this->input->get('account_id',true);
		$status = $this->input->get('check_status',true);
		$remark = $this->input->get('auth_finance_remark',true);
		
        if(empty($accId)){
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'参数有误');
		}

		if($status == '3' && empty($remark)){
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'未填写审核失败原因');
		
        }

		$res = $this->ChannelManager->modifyFinanceStatus($accId,$status,$remark);
        
        if($res){
            if($res == '2'){
			    return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'此账号未提交财务信息,无法审核');
            }
            return $this->outJson('',ErrCode::OK,'审核完成');
        }else{
            if($status == '2'){
			    return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'此账号已通过审核,请勿重复操作');
            }elseif($status == '3'){
			    return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'此账号已审核失败,请勿重复操作');
            }else{
			    return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'参数有误');
            }
		}
	}
}
?>
