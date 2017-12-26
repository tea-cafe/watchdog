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

        $sqlCount = 'SELECT COUNT(DISTINCT account_id) AS intcount From monthly_bill WHERE create_time>=' . $timeStart;
        $arrCount = $this->db->query($sqlCount)->result_array();
        if ($this->db->error()['code'] !== 0) {
            ErrCode::$msg = '月账单信息获取失败';
            return [];
        }
        $intCount = intval($arrCount[0]['intcount']);

        $sql = 'SELECT account_id,SUM(money) as money From monthly_bill WHERE create_time>=' . $timeStart . ' group by account_id limit ' . $rn*($pn-1) . ',' . $rn;
        $arrProfitLastMonth = $this->db->query($sql)->result_array();
        if ($this->db->error()['code'] !== 0) {
            ErrCode::$msg = '月账单获取失败';
            return [];
        }
        $strSqlIn = '(';
        foreach ($arrProfitLastMonth as $arrVal) {
            $strSqlIn .= "'" . $arrVal['account_id'] . "',";  
        }
        $strSqlIn = substr($strSqlIn, 0, -1) . ')';

        $strSqlSelectAccountBalance = 'SELECT account_id,money FROM account_balance where account_id in ' . $strSqlIn;
        $arrPreAccountBalance = $this->db->query($sql)->result_array();
        if ($this->db->error()['code'] !== 0) {
            ErrCode::$msg = '账户余额获取失败';
            return [];
        }
        $arrFormatAccountBalance = [];
        foreach ($arrPreAccountBalance as $val) {
            $arrFormatAccountBalance[$val['account_id']] = $val['money'];
        }

        $strSqlSelectEmail = 'SELECT account_id,email FROM account_info WHERE account_id IN ' . $strSqlIn;
        $arrEmails = $this->db->query($strSqlSelectEmail)->result_array();
        if ($this->db->error()['code'] !== 0
        || empty($arrEmails)) {
            ErrCode::$msg = '用户信息获取失败';
            return [];
        }
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
                $arrTmp['pre_account'] = 0;
            } else {
                $arrTmp['pre_account'] = intval($arrFormatAccountBalance[$arrBillList['account_id']]);
            }
            $arrTmp['post_balance'] = $arrTmp['pre_account'] + intval($arrBillList['money']);
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
