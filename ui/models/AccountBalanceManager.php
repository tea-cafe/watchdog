<?php
class AccountBalanceManager extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     *
     *
     */
    public function getPreAccountBalanceList($pn, $rn) {
        $timeNow = time();
        $timeStart = mktime(0,0,0,date("m",$timeNow)-1,1,date("Y",$timeNow));
        $timeEnd = mktime(0,0,0,date("m",$timeNow),1,date("Y",$timeNow));

        $sqlSelectMonthlyAction = 'SELECT action from monthly_action WHERE action_time>=' . $timeStart;
        $objMonthlyAction = $this->db->query($sqlSelectMonthlyAction);
        if ($this->db->error()['code'] !== 0) {
            ErrCode::$msg = '月账单操作信息失败';
            return [];
        }
        $arrMonthlyAction = $objMonthlyAction->result_array();
        if (empty($arrMonthlyAction[0]['action'])
            || $arrMonthlyAction[0]['action'] == 0) {
            ErrCode::$msg = '月账单还未生成';
            return [];
        }

        $sqlCount = 'SELECT COUNT(DISTINCT account_id) AS intcount From monthly_bill WHERE create_time>=' . $timeStart;
        $objCount = $this->db->query($sqlCount);
        if ($this->db->error()['code'] !== 0) {
            ErrCode::$msg = '月账单信息获取失败';
            return [];
        }
        $arrCount = $objCount->result_array();
        $intCount = intval($arrCount[0]['intcount']);

        $sql = 'SELECT account_id,SUM(money) as money From monthly_bill WHERE create_time>=' . $timeStart . ' group by account_id limit ' . $rn*($pn-1) . ',' . $rn;
        $objProfitLastMonth = $this->db->query($sql);
        if ($this->db->error()['code'] !== 0) {
            ErrCode::$msg = '月账单获取失败';
            return [];
        }
        $arrProfitLastMonth = $objProfitLastMonth->result_array();
        if (empty($arrProfitLastMonth)) {
            ErrCode::$msg = '请确认月账单是否已生成';
            return [];
        }
        $strSqlIn = '(';
        foreach ($arrProfitLastMonth as $arrVal) {
            $strSqlIn .= "'" . $arrVal['account_id'] . "',";  
        }
        $strSqlIn = substr($strSqlIn, 0, -1) . ')';

        $strSqlSelectAccountBalance = 'SELECT account_id,money FROM account_balance where account_id in ' . $strSqlIn;

        $objPreAccountBalance = $this->db->query($strSqlSelectAccountBalance);
        if ($this->db->error()['code'] !== 0) {
            ErrCode::$msg = '账户余额获取失败';
            return [];
        }
        $arrPreAccountBalance = $objPreAccountBalance->result_array();
        $arrFormatAccountBalance = [];
        foreach ($arrPreAccountBalance as $val) {
            $arrFormatAccountBalance[$val['account_id']] = $val['money'];
        }

        $strSqlSelectEmail = 'SELECT account_id,email FROM account_info WHERE account_id IN ' . $strSqlIn;
        $objEmails = $this->db->query($strSqlSelectEmail);
        if ($this->db->error()['code'] !== 0) {
            ErrCode::$msg = '用户信息获取失败';
            return [];
        }
        $arrEmails = $objEmails->result_array();
        $arrFormatEmails = [];
        foreach($arrEmails as $arrEmail) {
            $arrFormatEmails[$arrEmail['account_id']] = $arrEmail['email'];
        }

        $arrRes = [];
        $arrTmp = [];
        foreach($arrProfitLastMonth as $arrBillList) {
            $arrTmp['account_id'] = $arrBillList['account_id'];
            $arrTmp['email'] = $arrFormatEmails[$arrBillList['account_id']];
            if (empty($arrFormatAccountBalance[$arrBillList['account_id']])) {
                $arrTmp['pre_balance'] = 0;
            } else {
                $arrTmp['pre_balance'] = $arrFormatAccountBalance[$arrBillList['account_id']];
            }
            if ($arrMonthlyAction[0]['action'] == 1) {
                $arrTmp['post_balance'] = $arrTmp['pre_balance'] + $arrBillList['money'];
            } else {
                $arrTmp['post_balance'] = '-';
            }
            $arrTmp['period'] = date('Y-m', $timeStart); 
            $arrRes[] = $arrTmp;
            $arrTmp = [];
        }
        return [
            'list' => $arrRes,
            'pagination' => [
                'total' => $intCount, 
                'pageSize' => $rn, 
                'current' => $pn,
                
            ],
        ];
    }
}
