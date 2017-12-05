<?php
class AdSlotManager extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function insertAdSlotStyle($sql) {

        $this->load->database();
        $arrRes = $this->db->query($sql);
        var_dump($arrRes);exit;

    }

}
