<?php
class AccountBalanceManager extends CI_Model {

    const TAB_ACCOUNT_BALANCE = 'account_balance';
    const TAB_MONTHLY_BILL = 'monthly_bill';
    const TAB_MONTHLY_ACTION = 'monthly_action';

    public function __construct() {
        parent::__construct(); 
        $this->load->database();
    }

    public function doRollbackAccountBalance() {
        $this->load->database();

        $timeNow = time();
        $timeStart = mktime(0,0,0,date("m",$timeNow)-1,1,date("Y",$timeNow));
        $timeEnd = mktime(0,0,0,date("m",$timeNow),1,date("Y",$timeNow));

        $sql = 'SELECT account_id,create_time,SUM(money) as money From monthly_bill WHERE create_time>=0 group by account_id';
        $arrRes = $this->db->query($sql);
        if ($this->db->error()['code'] !== 0) {
            ErrCode::$msg = 'get monthly_bill shibai : ' . $this->db->error()['message']);
            return false;
        }

        $strSqlRollbackAccountBalance = 'INSERT INTO account_balance(account_id,update_time,money) VALUES'; 
        foreach ($arrRes->result_array() as $k => $val) {
            $strSqlRollbackAccountBalance .= "('" . $val['account_id'] . "',"
                . $val['create_time'] . ","
                . $val['money'] . "),"; 
        }
        $strSqlRollbackAccountBalance = substr($strSqlRollbackAccountBalance, 0, -1);
        $strSqlRollbackAccountBalance .= ' ON DUPLICATE KEY UPDATE update_time=VALUES(create_time),money=money-VALUES(money)';

        $strSqlUpdateMonthlyAction = 'INSERT INTO monthly_action(action_time,action) VALUES(' . $dateStart . ',1)ON DUPLICATE KEY UPDATE action_time=VALUES(' . $action_time . '),action=1';

        // 事务start
        $this->db->trans_start();
        $this->db->query($strSqlRollbackAccountBalance);
		$this->db->query($strSqlMergeAccountBalance);
		$this->db->trans_complete();
        // 事务 end
		if ($this->db->trans_status() === false) {
	        log_message('error', 'doMergeAccountBalance transmition execute failed');	
            return false;
        }
        return true


    }


    public function doMergeAccountBalance() {
        $this->load->database();

        $timeNow = time();
        $timeStart = mktime(0,0,0,date("m",$timeNow)-1,1,date("Y",$timeNow));
        $timeEnd = mktime(0,0,0,date("m",$timeNow),1,date("Y",$timeNow));

        $sql = 'SELECT account_id,create_time,SUM(money) as money From monthly_bill WHERE create_time>=0 group by account_id';
        $arrRes = $this->db->query($sql);
        if ($this->db->error()['code'] !== 0) {
            ErrCode::$msg = 'get monthly_bill shibai : ' . $this->db->error()['message']);
            return false;
        }

        $strSqlMergeAccountBalance = 'INSERT INTO account_balance(account_id,update_time,money) VALUES'; 
        foreach ($arrRes->result_array() as $k => $val) {
            $strSqlMergeAccountBalance .= "('" . $val['account_id'] . "',"
                . $val['create_time'] . ","
                . $val['money'] . "),"; 
        }
        $strSqlMergeAccountBalance = substr($sql, 0, -1);
        $strSqlMergeAccountBalance .= ' ON DUPLICATE KEY UPDATE update_time=VALUES(create_time),money=money+VALUES(money)';

        $strSqlUpdateMonthlyAction = 'INSERT INTO monthly_action(action_time,action) VALUES(' . $dateStart . ',1)ON DUPLICATE KEY UPDATE action_time=VALUES(' . $action_time . '),action=1';

        // 事务start
        $this->db->trans_start();
        $this->db->query($strSqlMergeAccountBalance);
		$this->db->query($strSqlMergeAccountBalance);
		$this->db->trans_complete();
        // 事务 end
		if ($this->db->trans_status() === false) {
	        log_message('error', 'doMergeAccountBalance transmition execute failed');	
            return false;
        }
        return true
    }

    /**
     *
     *
     */
    public function getAccountBalanceAction() {
        $date =  strtotime(date('Y-m') . ' -1 month');
        $this->db->select('action');
        $this->db->where('action_time', intval($date)); 
        $arrRes = $this->db->get(self::TAB_MONTHLY_ACTION)->result_array();
        $arrErr = $this->db->error();
        if ($arrErr['code'] === 0) {
            if (empty($arrRes)
                || $arrRes[0]['action'] === 0) {
                return 0;
            }
            if ($arrRes[0]['action'] === 1) {
                return 1
            }   
        }
        return -1;
    }



}
