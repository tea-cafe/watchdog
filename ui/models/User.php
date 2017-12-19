<?php
/**
 * 原始redis session 数据：$this->redisutil->get('HPREDIS_SESSION:' . session_id()));
 *
 * $user = [
 *     'pk' => primary_key(id), 用于所有的登陆后查询
 *     'username' => 'xxx',
 * ]
 *
 */

class User extends CI_Model {

    const EXPIRE_SESSION = 3600; // 秒 

	/**
     *
	 */
    public function __construct() {
        parent::__construct();
        session_start();
    }

	/**
     * @param string $strUserName
     * @param string $strPasswd
     * @return bool
	 */
    public function doLogin($strUserName, $strPasswd) {
        // 查询数据库 验证账户密码
        $this->load->library('DbUtil');
        $arrFields = [
            'select' => 'account_id,email,contact_person,company',
            'where' => 'email=\'' . $strUserName . '\''
                . ' AND passwd=\'' . md5($strPasswd) . '\'' 
                . ' AND create_time>0 AND update_time>0',
            'order_by' => 'passwd DESC',
            'limit' => '0,1',
        ];
        $arrRes = $this->dbutil->getAccount($arrFields);
        if (empty($arrRes)) {
            return false;
        }
        
        $_SESSION['login_time'] = time();
        $_SESSION['account_id'] = $arrRes[0]['account_id'];
        $_SESSION['email'] = $arrRes[0]['email'];
        $_SESSION['username'] = empty($arrRes[0]['company']) ? $arrRes[0]['contact_person'] : $arrRes[0]['company'];
        return true;
    }

	/**
     * @return array
	 */
    public function checkLogin() {
        if (isset($_SESSION['login_time'])
            && isset($_SESSION['account_id'])
            && isset($_SESSION['email'])
            && isset($_SESSION['username'])
            && (time() - $_SESSION['login_time']) <= self::EXPIRE_SESSION) {
            /* 更新session时间 */
            $_SESSION['login_time'] = time();

            return [
                'account_id' => $_SESSION['account_id'],
                'email' => $_SESSION['email'],
                'username' => $_SESSION['username'],
            ];
        }
        return [];
    } 

	/**
     *
	 */
    public function clearLoginInfo() {
        setcookie('XDL_SSP', '', time()-1, '/');
        $_SESSION = [];
        return true;
    }
}
