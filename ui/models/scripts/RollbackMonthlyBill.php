<?php
/**
 *
 */
class RollbackMonthlyBill extends CI_Model {

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
                'message' => 'ERROR: 月账单' . date('Y-m', $date) . '还未生成,禁止回滚',
            ];
        }
        if (!empty($arrRes)
            && $arrRes[0]['action'] != 1) {
            return [
                'code' => 1,
                'message' => 'ERROR: 月账单' . date('Y-m', $date) . '禁止回滚,如果要回滚月账单，请先确保账户余额已回滚完成',
            ];
            return;
        }

        $timeNow = time();
        $timeStart = mktime(0,0,0,date("m",$timeNow)-1,1,date("Y",$timeNow));
        $timeEnd = mktime(0,0,0,date("m",$timeNow),1,date("Y",$timeNow));

        $sqlForMonthlyBill = 'DELETE FROM monthly_bill WHERE time>=' . $timeStart . ' AND time<=' . $timeEnd;

        // 更新monthly_action 为1
        $sqlForMonthlyAction = 'INSERT INTO monthly_action(action_time, action) VALUES(' . $date . ',0) ON DUPLICATE KEY UPDATE action=VALUES(action)';

        $this->db->trans_begin();
        $this->db->query($sqlForMonthlyBill);
        if ($this->db->affected_rows() === 0) {
            $this->db->trans_rollback();
            return [
                'code' => 1,
                'message' => '月账单' . date('Y-m', $date) . '删除失败，请检查monthly_bill',
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
                'message' => '月账单回滚事务失败，请重试',
            ];
        } else {
            $this->db->trans_commit();
            return [
                'code' => 0,
                'message' => '月账单回滚成功',
            ];
        }

    }
}
