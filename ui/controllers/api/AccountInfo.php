<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 接口 用户信息
 */

class AccountInfo extends MY_Controller {

    /*
     *
     */
    public function __construct() {
        parent::__construct();
        $this->load->model('User');
        $this->arrUser = $this->User->checkLogin();
    }

	/**
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
        if (empty($this->arrUser)) {
            return $this->outJson('', ErrCode::ERR_NOT_LOGIN);
        }
        
        $this->load->model('Account');
        $arrInfo = $this->Account->getInfo($this->arrUser['account_id']);

        if(empty($arrInfo)){
            return $this->outJson('',ErrCode::ERR_SYSTEM,ErrCode::$msg);
        }
        
        return $this->outJson($arrInfo, ErrCode::OK,'获取成功');
	}
}
