<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 后台 登陆
 */
class User extends BG_Controller {
	public function __construct(){
		parent::__construct();
		$this->load->model('UserManager');
	}

	public function login(){
        $arrPostParam = json_decode(file_get_contents('php://input'), true);
		$userName = $arrPostParam['username'];
		$passWord = $arrPostParam['password'];
		$loginRes = $this->UserManager->doLogin($userName,$passWord);
		
		if($loginRes){
			return $this->outJson('',ErrCode::OK,'登陆成功');
		}else{
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'登陆失败');
		}
    }

    /* 检测登陆状态*/
    public function checkStatus(){
        if(empty($this->arrUser)){
            return $this->outJson('', ErrCode::ERR_NOT_LOGIN);
        }
        $data['bg_email'] = $this->arrUser['bg_email'];
        return $this->outJson($data,ErrCode::OK, '登录成功');
    }

    /*退出登陆*/
    public function logout(){
        $res = $this->UserManager->clearLoginInfo();
        if($res){
            return $this->outJson('',ErrCode::OK,'退出登陆');
        }else{
            return $this->outJson('', ErrCode::ERR_INVALID_PARAMS,'退出失败');
        }
    }
}

