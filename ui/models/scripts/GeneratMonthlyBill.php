<?php
/**
 * INSERT INTO monthly_bill(app_id,account_id,money) (SELECT app_id,acct_id,SUM(post_profit) FROM tab_media_user_profit_sum_daily WHERE create_time>2000 AND create_time<10000 GROUP BY app_id)
 *
 */
class GeneratMonthlyBills extends CI_Model {

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
        if (!empty($arrRes)
            && $arrRes[0]['action'] > 0) {
            return [
                'code' => 1,
                'mssage' => 'ERROR: monthly bill for ' . date('Y-m', $date) . 'has generaged',
            ];
        }

        $dateStart =  date('Y-m-d', strtotime(date('Y-m-01') . ' -1 month'));
        $dateLast = date('Y-m-d', strtotime(date('Y-m-01') . ' -1 day'));


        $sqlForMonthlyBill = 'INSERT INTO monthly_bill(app_id,time,account_id,media_name,media_platform,create_time,money) (SELECT app_id,UNIX_TIMESTAMP(`date`),account_id,platform,media_name,UNIX_TIMESTAMP(`date`),SUM(post_profit) From tab_media_user_profit_sum_daily  WHERE `date` BETWEEN \'' . $dateStart . '\' AND \'' . $dateLast . '\' group by app_id)';

        // 更新monthly_action 为1
        $sqlForMonthlyAction = 'INSERT INTO monthly_action(action_time, action) VALUES(' . $date . ',1) ON DUPLICATE KEY UPDATE action=VALUES(action)';

        $this->db->trans_begin();
        $this->db->query($sqlForMonthlyBill);
        if ($this->db->affected_rows() === 0) {
            $this->db->trans_rollback();
            return [
                'code' => 2,
                'message' => '月账单' . date('Y-m', $date) . '插入失败，请检查monthly_bill',
            ];
        }
        $this->db->query($sqlForMonthlyAction);
        if ($this->db->affected_rows() === 0) {
            $this->db->trans_rollback();
            return [
                'code' => -1,
                'message' => '月账单' . date('Y-m', $date) . '操作记录插入失败，请检查monthly_bill',
            ];
            return;
        }
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return [
                'code' => -1,
                'message' => '月账单生事务失败，请重试',
            ];
        } else {
            $this->db->trans_commit();
            return [
                'code' => 0,
                'message' => '月账单生成成功',
            ];
        }

    }
}
