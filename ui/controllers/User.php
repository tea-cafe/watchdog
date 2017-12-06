<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 后台 登陆
 */
class User extends BG_Controller {
	public function __construct(){
		parent::__construct();
	}

	public function Login(){
        $arrPostParam = json_decode(file_get_contents('php://input'), true);
		$userName = $arrPostParam['username'];
		$passWord = $arrPostParam['password'];
		$this->load->model('bg/FunUser');
		$loginRes = $this->FunUser->doLogin($userName,$passWord);
		
		if($loginRes){
			return $this->outJson('',ErrCode::OK,'登陆成功');
		}else{
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'登陆失败');
		}
	}
}

