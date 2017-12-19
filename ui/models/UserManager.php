<?php
/**
 * 后台登陆
 */
class UserManager extends CI_Model {

    const EXPIRE_SESSION = 86400;

	public function __construct(){
		parent::__construct();
        session_start();
	}

	public function doLogin($userName,$passWord){
		$params = array(
			'select' => '',
			'where' => 'username = "'.$userName.'"'.' AND password = "'.md5($passWord).'"',
		);
		$this->load->library('DbUtil');
		$userRes = $this->dbutil->getBgUser($params);
		if(empty($userRes)){
			return false;
		}
		$_SESSION['bg_login_time'] = time();
		$_SESSION['account_id'] = $userRes[0]['id'];
		$_SESSION['bg_email'] = $userRes[0]['username'];
        return true;
	}

	/**
     * @return array
	 */
    public function checkLogin() {
        if (isset($_SESSION['bg_login_time'])
            && isset($_SESSION['account_id'])
            && isset($_SESSION['bg_email'])
            && (time() - $_SESSION['bg_login_time']) <= self::EXPIRE_SESSION) {
            /* 更新session时间 */
            $_SESSION['bg_login_time'] = time();
            
            return [
                'account_id' => $_SESSION['account_id'],
                'bg_email' => $_SESSION['bg_email'],
            ];
        }
        return [];
    }

	/**
     * 退出登录,清除SESSION
	 */
    public function clearLoginInfo() {
        setcookie('BGXDL_SSP', '', time()-1, '/');
        $_SESSION = [];
        return true;
    }
}


?>
