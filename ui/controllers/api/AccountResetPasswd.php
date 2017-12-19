<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 重置密码
 */
class AccountResetPasswd extends MY_Controller{
	public function __construct(){
		parent::__construct();
	}

	public function index(){
		$email = $this->input->get('email',true);

		if(empty($email) || !stristr($email,"@")){
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'参数错误');
		}

		$this->load->model("Account");
		$result = $this->Account->resetPwdCode($email);
		
		if($result === 2){
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'没有此账号');
		}else if(!$result){
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'验证码发送失败');
		}else{
			$data['data']['email'] = $email;
			return $this->outJson($data,ErrCode::OK,'验证码发送成功');
		}
	}

	public function CheckCode(){
        //$arrPostParams = json_decode(file_get_contents('php://input'), true);
        //$VerifyCode = $arrPostParams['verifycode'];
		//$email = $arrPostParams['email'];
		$VerifyCode = $this->input->get('verifycode',true);
		$email = $this->input->get('email',true);

		if(empty($VerifyCode) || empty($email) || !stristr($email,'@')){
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'参数错误');
		}

		$this->load->library("RedisUtil");
		$RdsKey = 'ResetPwd_'.$email;
		$RdsValue = $this->redisutil->get($RdsKey);
		$RdsValue = unserialize($RdsValue);

		if($VerifyCode == $RdsValue['code'] && $email == $RdsValue['email']){
            $this->load->helper('createkey');
            $strToken = keys(32);

			$data['email'] = $email;
			$data['strToken'] = $strToken;
		    
			$RdsValue['strToken'] = $strToken;
			$this->redisutil->set($RdsKey,serialize($RdsValue));
			$this->redisutil->expire($RdsKey,60*60);
			return $this->outJson($data,ErrCode::OK,'验证成功');
        }else{
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'验证码错误');
		}
	}

	public function ModifyPwd(){
        //$arrPostParams = json_decode(file_get_contents('php://input'), true);
		//$email = $arrPostParams['email'];
		//$strToken = $arrPostParams['strToken'];
		//$newPwd = $arrPostParams['newpwd'];
		//$confirmPwd = $arrPostParams['confirmpwd'];
		$email = $this->input->get('email',true);
		$strToken = $this->input->get('strToken',true);
		$newPwd = $this->input->get('password',true);
		$confirmPwd = $this->input->get('confirm',true);

        if(empty($email) || empty($newPwd) || empty($confirmPwd) || empty($strToken)){
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'参数错误');
		}
		
		if($newPwd !== $confirmPwd){
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'密码输入不一致');
        }

		$this->load->library("RedisUtil");
		$RdsKey = 'ResetPwd_'.$email;
		$RdsValue = $this->redisutil->get($RdsKey);
		$RdsValue = unserialize($RdsValue);
        
        if($RdsValue['strToken'] == $strToken){
			$this->load->model('Account');
			$result = $this->Account->UpdatePwd($email,$newPwd,$confirmPwd);
			if($result){
                $this->redisutil->delete($RdsKey);
                return $this->outJson('',ErrCode::OK,'密码重置成功');
			}else{
			    return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'密码重置失败');
			}
		}else{
            $this->redisutil->delete($RdsKey);
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'请重新获取验证码');
		}
	}
}
?>
