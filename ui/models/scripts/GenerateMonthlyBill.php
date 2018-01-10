<?php
/**
 * monthly_action action 状态说明: (月账单 指 上月的账单)
 * 0 : 月账单未生成
 * 1 : 月账单已生成
 * 2 : 月账单已合入余额表
 */
class GenerateMonthlyBill extends CI_Model {

    const MAIL_RECEIVERS = [
        '345627116@qq.com',
        'szishuo@163.com',
        '719559506@qq.com',
    ];

    public function __construct() {
        parent::__construct();
    }

    public function do_execute() {

        $this->load->database();

        $this->load->library('Mailer');

        $date = strtotime(date('Y-m-01') . ' -1 month');

        // 查询 monthly_action 获取月账单操作状态, 时间以上月1号0点时间戳为基准
        $sqlCheck = 'SELECT action FROM monthly_action WHERE action_time=' . $date;
        $objRes = $this->db->query($sqlCheck);
        $arrErr = $this->db->error();
        if ($arrErr['code'] !== 0) {
            return [
                'code' => -1,
                'message' => 'Error : 请检查 monthly_action的action状态 ' . $arrErr['message'],
            ];
        }
        $arrRes = $objRes->result_array();
        if (!empty($arrRes)
            && $arrRes[0]['action'] > 0) {
            return [
                'code' => 1,
                'mssage' => 'ERROR: 月账单' . date('Y-m', $date) . '已生成，请勿重复操作',
            ];
        }

        $dateStart =  date('Y-m-d', strtotime(date('Y-m-01') . ' -1 month'));
        $dateLast = date('Y-m-d', strtotime(date('Y-m-01') . ' -1 day'));

        $sqlForMonthlyBill = 'INSERT INTO monthly_bill(app_id,time,account_id,media_name,media_platform,create_time,money) (SELECT app_id,UNIX_TIMESTAMP(`date`),account_id,platform,media_name,UNIX_TIMESTAMP(`date`),SUM(post_profit) From tab_media_user_profit_sum_daily  WHERE `date` BETWEEN \'' . $dateStart . '\' AND \'' . $dateLast . '\' group by app_id)';

        // 更新monthly_action 为1
        $sqlForMonthlyAction = 'INSERT INTO monthly_action(action_time,create_time,update_time,action) VALUES(' . $date . ',' . time() . ',' . time() . ',1) ON DUPLICATE KEY UPDATE action=VALUES(action)';

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

        //$arrRes = $this->db->query('select action from monthly_action')->result_array();
        //if ($arrRes[0]['action'] == 1) {
        //    $this->db->trans_rollback();
        //    return [
        //        'code' => -1,
        //        'message' => '测试失败专用，看月账单是否插入',
        //    ];

        //}

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $this->mailer->sendMails(
                self::MAIL_RECEIVERS, 
                'WARNING:月账单生成失败报警', 
                '月账单'. date('Y-m', $date) . '生成失败',
                '月账单'. date('Y-m', $date) . '生成失败.');
            return [
                'code' => -1,
                'message' => '月账单生事务失败，请重试',
            ];
        } else {
            $this->db->trans_commit();
            $this->mailer->sendMails(
                self::MAIL_RECEIVERS, 
                'SUCCESS:月账单生成失败报警', 
                '月账单'. date('Y-m', $date) . '生成成功',
                '月账单'. date('Y-m', $date) . '生成成功.');
            return [
                'code' => 0,
                'message' => '月账单生成成功',
            ];
        }
    }
}
