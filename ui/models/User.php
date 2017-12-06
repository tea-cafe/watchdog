<?php
/**
 * 后台登陆
 */
class User extends CI_Model {

    const EXPIRE_SESSION = 86400;

	public function __construct(){
		parent::__construct();
        session_start();
	}

	public function doLogin($userName,$passWord){
		$params = array(
			'select' => '',
			'where' => 'username = "'.$userName.'"'.' AND password = "'.md5($passWord).'"',
			'order_by' => '',
			'limit' => '',
		);
		$this->load->library('DbUtil');
		$userRes = $this->dbutil->getBgUser($params);

		if(empty($userRes)){
			return false;
		}
		$_SESSION['login_time'] = time();
		$_SESSION['bg_account_id'] = $userRes[0]['id'];
		$_SESSION['email'] = $userRes[0]['username'];
        return true;
	}

	/**
     * @return array
	 */
    public function checkLogin() {
        if (isset($_SESSION['login_time'])
            && isset($_SESSION['bg_account_id'])
            && isset($_SESSION['email'])
            && (time() - $_SESSION['login_time']) <= self::EXPIRE_SESSION) {
            return [
                'bg_account_id' => $_SESSION['bg_account_id'],
                'email' => $_SESSION['email'],
            ];
        }
        return [];
    } 

}
?>
