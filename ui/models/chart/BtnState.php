<?php
class BtnState extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->library('DbUtil');
    }

    public function getBtnState($arrSelect) {
        $arrRes = $this->dbutil->getProcessState($arrSelect);
        return $arrRes;
    }

    public function  insertBtnState($arrParams) {
        //foreach($arrParams as $val) {
            $sqlString = '('."'".implode( "','", $arrParams ) . "'".')'; //批量
            $insertRows[] = $sqlString;
            $strValues = implode(',', $insertRows);
        //}
        $fields = "btn_load=". $arrParams['btn_load'] .","
            ."btn_load_cancel=".$arrParams['btn_load_cancel'].","
            ."platform_en='". $arrParams['platform_en']."',"
            ."btn_select=".$arrParams['btn_select'].","
            ."update_time=".time();

            $sql = "INSERT IGNORE INTO tab_process_state(user,btn_load,btn_load_cancel,btn_sum,btn_sum_cancel,create_time,update_time,platform_en,btn_select,date) 
                VALUES {$strValues} ON DUPLICATE KEY UPDATE $fields";
        $boolRes = $this->dbutil->query($sql);
        return $boolRes;
    }

    public function updateBtnState($arrParams) {
        $arrRes = $this->dbutil->udpProcessState($arrParams);
        return $arrRes;
    }

    public function checkBtnState($arrParams) {
        $arrRes = $this->dbutil->getProcessState($arrParams);
        return $arrRes;
    }
}
