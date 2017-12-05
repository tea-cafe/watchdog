<?php
/**
 * 账户相关 总类
 */


class Account extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 获取媒体信息
     */
    public function getAccountInfo() {
        $this->load->model('account/Info');
        $arrData = $this->Lists->getInfo();
        return $arrData;
    } 

    /**
     * 账户基本信息注册
     * @param array $arrParams
     * @return array
     */
    public function insertAccountBaseInfo($arrParams) {
        $this->load->library('DbUtil');
        $arrRes = $this->dbutil->setAccount($arrParams);
        return $arrRes;
    }

    /**
     * 用户基本信息修改
     * @param array
     * @return bool
     */
    public function updateAccountBaseInfo($arrParams) {
        $this->load->library('DbUtil');
        $bolRes = $this->dbutil->udpAccount($arrParams);
        return $bolRes;
    }

    /**
     * 账户财务信息提交
     * @param array $arrParams
     * @return bool
     */
    public function updateAccountFinanceInfo($arrParams) {
        $this->load->library('DbUtil');
        $bolRes = $this->dbutil->udpAccount($arrParams);
        return $bolRes;
	}

	/**
	 * 获取重置密码的验证码
	 */
	public function resetPwdCode($email){
		var_dump($email);
		$where = array(
			'select' => '',
			'where' => 'email = "'.$email.'"',
			'order_by' => '',
			'limit' => '',
		);
		$this->load->library("DbUtil");
		$result = $this->dbutil->getAccount($where);

		if(empty($result) || count($result) == 0){
			$res = 2;
			return $res;
		}

		$this->load->library("RedisUtil");
		$token = '19961024';
		$RdsKey = 'ResetPwd_'.$email;
		$RdsValue = array(
			'email' => $email,
			'code' => $token,
		);

		$this->load->library('email');
		$this->email->from('15911129682@163.com', 'SSP平台');
		$this->email->to($email);
		$this->email->subject('SSP账号密码重置');
		$this->email->message('您的验证码为：'.$token.',5分钟内输入有效');

		$res = $this->email->send();
		//echo $this->email->print_debugger();
		
		if($res){
			$this->redisutil->set($RdsKey,serialize($RdsValue));
			$this->redisutil->expire($RdsKey,60*5);
		}

		return $res;
	}

	/**
	 * 重置密码
	 */
	public function UpdatePwd($email,$newPwd,$confirmPwd){
		$where = array(
			'passwd' => md5($newPwd),
			'where' => 'email = "'.$email.'"',
		);

		$this->load->library('DbUtil');
		$result = $this->dbutil->udpAccount($where);

		if($result['code'] == 0){
			return true;
		}else{
			return false;
		}
	}
}
?>
