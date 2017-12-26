<?php
class AccountBalanceManager extends CI_Model {

    public function __construct() {
        parent::__construct(); 
        $this->load->database();
    }

    public function getPreAccountBalanceList() {
        $timeNow = time();
        $timeStart = mktime(0,0,0,date("m",$timeNow)-1,1,date("Y",$timeNow));
        $timeEnd = mktime(0,0,0,date("m",$timeNow),1,date("Y",$timeNow));

        $sql = 'SELECT account_id,app_id,money From monthly_bill WHERE create_time>=' . $timeStart . ' group by app_id';
        $arrProfitLastMonth = $this->db->query($sql)->result_array();
        if ($this->db->error()['code'] !== 0) {
            ErrCode::$msg = '月账单获取失败';
            return [];
        }

        $strSqlSelectAccountBalance = 'SELECT account_id,money FROM account_balance'; 
        $arrPreAccountBalance = $this->db->query($sql)->result_array();
        if ($this->db->error()['code'] !== 0) {
            ErrCode::$msg = '账户余额获取失败';
            return [];
        }
        $arrFormatAccountBalance = [];
        foreach ($arrPreAccountBalance as $val) {
            $arrFormatAccountBalance[$val['account_id']] = $val['money'];
        }

        $arrRes = [];
        foreach($arrProfitLastMonth as $arrBillList) {
            $arrRes[$arrBillList['account_id']]['bill_list'][] = [
                'app_id' => $arrBillList['app_id'],
                'money' => $arrBillList['money'],
            ];
            if (empty($arrFormatAccountBalance[$arrBillList['account_id']])) {
                $arrFormatAccountBalance[$arrBillList['account_id']] = 0;
            }
            $arrRes[$arrBillList['account_id']]['pre_account'] = intval($arrFormatAccountBalance[$arrBillList['account_id']]);
            if (!isset($arrRes[$arrBillList['account_id']]['post_balance'])) {
                $arrRes[$arrBillList['account_id']]['post_balance'] = $arrFormatAccountBalance[$arrBillList['account_id']];
            }
            $arrRes[$arrBillList['account_id']]['post_balance'] += $arrBillList['money'];
        }
        return $arrRes;
    }
}
