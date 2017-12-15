<?php
/**
 * $sql = 'INSERT INTO account_balance(account_id,update_time,money) (SELECT account_id,update_time,SUM(money) as money From monthly_bill WHERE create_time>=' . $timeStart . ' AND create_time<' . $timeEnd . ' group by account_id) ON DUPLICATE KEY UPDATE money=money+VALUES(money),update_time=VALUES(update_time)';
 *
 */
class GeneratChannelBalance extends CI_Model {
        
    public function do_execute() {

        $timeNow = time();
        $timeStart = mktime(0,0,0,date("m",$timeNow)-1,1,date("Y",$timeNow));
        $timeEnd = mktime(0,0,0,date("m",$timeNow),1,date("Y",$timeNow));

        $this->load->database();
        $sql = 'SELECT account_id,create_time,SUM(money) as money From monthly_bill WHERE create_time>=0 group by account_id';
        $arrRes = $this->db->query($sql);
        if ($this->db->error()['code'] !== 0) {
            exit('chaxun monthly_bill shibai : ' . $this->db->error()['message']);
        }

        $sql = 'INSERT INTO account_balance(account_id,update_time,money) VALUES'; 
        foreach ($arrRes->result_array() as $k => $val) {
            $sql .= "('" . $val['account_id'] . "',"
                . $val['create_time'] . ","
                . $val['money'] . "),"; 
        }
        $sql = substr($sql, 0, -1);
        $sql .= ' ON DUPLICATE KEY UPDATE update_time=VALUES(create_time),money=money+VALUES(money)';
        $arrRes = $this->db->query($sql);
        if ($this->db->error()['code'] !== 0) {
            exit('insert account_balance shibai');
        }
        echo 'success';
    }
}
