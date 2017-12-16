<?php
/**
 *
 */
class GeneratMonthlyBills extends CI_Model {
        
    public function do_execute() {

        //$timeNow = time();
        //$timeStart = mktime(0,0,0,date("m",$timeNow)-1,1,date("Y",$timeNow));
        //$timeEnd = mktime(0,0,0,date("m",$timeNow),1,date("Y",$timeNow));
        $dateStart =  date('Y-m-d', strtotime(date('Y-m-01') . ' -1 month'));
        $dateLast = date('Y-m-d', strtotime(date('Y-m-01') . ' -1 day'));

        $sql = 'INSERT INTO monthly_bill(app_id,time,account_id,media_name,media_platform,create_time,money) (SELECT app_id,UNIX_TIMESTAMP(`date`),account_id,platform,media_name,UNIX_TIMESTAMP(`date`),SUM(post_profit) From tab_media_user_profit_sum_daily  WHERE `date` BETWEEN \'' . $dateStart . '\' AND \'' . $dateLast . '\' group by app_id)';
        $this->load->database();
        //$arrRes = $this->db->query('show tables');
        //var_dump($arrRes->result_array());
        $bolRes = $this->db->query($sql);
        $error = $this->db->error();
        echo '影响行数 : ' .  $this->db->affected_rows();
        var_dump($bolRes, $error);
    }
}
