<?php
/**
 * $sql = 'INSERT INTO account_balance(account_id,update_time,money) (SELECT account_id,update_time,SUM(money) as money From monthly_bill WHERE create_time>=' . $timeStart . ' AND create_time<' . $timeEnd . ' group by account_id) ON DUPLICATE KEY UPDATE money=money+VALUES(money),update_time=VALUES(update_time)';
 *
 */
class RollbackChannelBalance extends CI_Model {
        
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
        if (!empty($arrRes)
            && $arrRes[0]['action'] != 2) {
            return [
                'code' => 1,
                'message' => 'ERROR: ' . date('Y-m', $date) . '的账单未合入余额，请勿回滚',
            ];
        }
            
        $timeNow = time();
        $timeStart = mktime(0,0,0,date("m",$timeNow)-1,1,date("Y",$timeNow));
        $timeEnd = mktime(0,0,0,date("m",$timeNow),1,date("Y",$timeNow));

        $sqlMonthlyBill = 'SELECT account_id,create_time,SUM(money) as money From monthly_bill WHERE create_time>=0 group by account_id';
        $arrRes = $this->db->query($sqlMonthlyBill);
        if ($this->db->error()['code'] !== 0) {
            return [
                'code' => -1,
                'message' => 'ERROR : 月账单查询失败，' . $this->db->error()['message'],
            ];
        }

        $sqlForAccountBalance = 'INSERT INTO account_balance(account_id,update_time,money) VALUES'; 
        foreach ($arrRes->result_array() as $k => $val) {
            $sqlForAccountBalance .= "('" . $val['account_id'] . "',"
                . $val['create_time'] . ","
                . $val['money'] . "),"; 
        }
        $sqlForAccountBalance = substr($sqlForAccountBalance, 0, -1);
        $sqlForAccountBalance .= ' ON DUPLICATE KEY UPDATE update_time=VALUES(create_time),money=money-VALUES(money)';

        // 回滚 monthly_action 到 1 
        $sqlForMonthlyAction = 'INSERT INTO monthly_action(action_time, action) VALUES(' . $date . ',1)  ON DUPLICATE KEY UPDATE action=VALUES(action)';

        // 事务 start
        $this->db->trans_begin();
        $this->db->query($sqlForAccountBalance);
        if ($this->db->affected_rows() === 0) {
            $this->db->trans_rollback();
            return [
                'code' => 1,
                'message' => '月账单' . date('Y-m', $date) . '回滚余额失败，请检查account_balance',
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
                'message' => '用户余额回滚事务失败，请重试',
            ];
        } else {
            $this->db->trans_commit();
            return [
                'code' => 0,
                'message' => '用户余额回滚成功',
            ];
        }
    }
}
