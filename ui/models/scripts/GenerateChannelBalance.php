<?php
/**
 * monthly_action action 状态说明: (月账单 指 上月的账单)
 * 0 : 月账单未生成
 * 1 : 月账单已生成
 * 2 : 月账单已合入余额表
 */
class GenerateChannelBalance extends CI_Model {

    public function __construct() {
        parent::__construct();
    }
        
    public function do_execute() {
        $this->load->database();

        // 查询 monthly_action 获取月账单操作状态, 时间以上月1号0点时间戳为基准
        $date = strtotime(date('Y-m-01') . ' -1 month');
        $sqlCheck = 'SELECT action FROM monthly_action WHERE action_time=' . $date;
        $objRes = $this->db->query($sqlCheck);
        $arrErr = $this->db->error();
        if ($arrErr['code'] !== 0) {
            return [
                'code' => -1,
                'message' => 'Error : check monthly_action action ' . $arrErr['message'],
            ];
        }
        $arrRes = $objRes->result_array();
        if (empty($arrRes)) {
            return [
                'code' => 1,
                'message' => 'ERROR: ' . date('Y-m', $date) . '的账单还未生成，请生成后再操作',
            ];
        }
        if (!empty($arrRes)) {
            if ($arrRes[0]['action'] != 1) {
                return [
                    'code' => 1,
                    'message' => 'ERROR: ' . date('Y-m', $date) . '的账单未生成或者已经合入余额，请勿重复操作',
                ];
            }
        }

        $timeNow = time();
        $timeLastMonth = mktime(0,0,0,date("m",$timeNow)-1,1,date("Y",$timeNow));

        $sql = 'SELECT account_id,time,SUM(money) as money FROM monthly_bill WHERE time>=' . $timeLastMonth . ' group by account_id';
        $arrRes = $this->db->query($sql);
        if ($this->db->error()['code'] !== 0) {
            return [
                'code' => -1,
                'message' => 'ERROR : 月账单查询失败，' . $this->db->error()['message'],
            ];
        }

        $sqlForAccountBalance = 'INSERT INTO account_balance(account_id,create_time,update_time,money) VALUES'; 
        foreach ($arrRes->result_array() as $k => $val) {
            $sqlForAccountBalance  .= "('" . $val['account_id'] . "',"
                . $val['time'] . ","
                . $val['time'] . ","
                . $val['money'] . "),"; 
        }
        $sqlForAccountBalance  = substr($sqlForAccountBalance , 0, -1);
        $sqlForAccountBalance .= ' ON DUPLICATE KEY UPDATE update_time=VALUES(create_time),money=money+VALUES(money)';

        $sqlForMonthlyAction = 'INSERT INTO monthly_action(action_time, action, update_time) VALUES(' . $date . ',2,' . time() . ')  ON DUPLICATE KEY UPDATE action=VALUES(action)';

        // 事务 start
        $this->db->trans_begin();
        $this->db->query($sqlForAccountBalance);
        if ($this->db->affected_rows() === 0) {
            $this->db->trans_rollback();
            return [
                'code' => 1,
                'message' => '月账单' . date('Y-m', $date) . '合入余额失败，请检查account_balance',
            ];
        }
        $this->db->query($sqlForMonthlyAction);
        if ($this->db->affected_rows() === 0) {
            $this->db->trans_rollback();
            return [
                'code' => 1,
                'message' => '月账单' . date('Y-m', $date) . '操作记录插入失败，请检查monthly_bill',
            ];
        }
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return [
                'code' => -1,
                'message' => '用户余额事务失败，请重试',
            ];
        } else {
            $this->db->trans_commit();
            return [
                'code' => 0,
                'message' => '用户余额合成成功',
            ];
        }
    }

}
