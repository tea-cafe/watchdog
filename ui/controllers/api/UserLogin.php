<?php
/**
 * 用户登录接口
 */

class UserLogin extends MY_Controller {

    public function __construct() {
        parent::__construct();
    }

    /**
     *登录接口
     */
    public function index() {
        $arrPostParams = json_decode(file_get_contents('php://input'), true);
        $strUserName = $arrPostParams['username'];
        $strPasswd = $arrPostParams['passwd'];
        
        if (empty($strUserName) || empty($strPasswd)) {
            return $this->outJson('', ErrCode::ERR_LOGIN_FAILED);
        }

        $this->load->model('User');
        $bolRes = $this->User->doLogin($strUserName, $strPasswd);
        if ($bolRes) {
            return $this->outJson('', ErrCode::OK, '登录成功');
        }
        return $this->outJson('', ErrCode::ERR_LOGIN_FAILED);
    }

    /**
     * 退出登陆
     */
    public function logout(){
        $res = $this->User->clearLoginInfo();
        if($res){
            return $this->outJson('',ErrCode::OK,'退出登录');
        }else{
            return $this->outJson('', ErrCode::ERR_INVALID_PARAMS,'退出失败'); 
        }
    }

    /**
     * 检测登陆状态
     */
    public function checkStatus(){
        if(empty($this->arrUser)){
            return $this->outJson('', ErrCode::ERR_NOT_LOGIN);
        }
        
        $data['email'] = $this->arrUser['email'];
        $data['username'] = $this->arrUser['username'];
        $data['account_id'] = $this->arrUser['account_id'];
            return $this->outJson($data,ErrCode::OK, '登录成功');
    }
}
