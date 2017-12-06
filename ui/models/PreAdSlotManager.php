<?php
class PreAdSlotManager extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->library('DbUtil');
    }

    public function insertPreAdSlot($strAppId, $jsonPreSlotIds) {
        $sql = 'INSERT INTO pre_adslot(app_id,data,update_time) VALUES' 
            . "('" . $strAppId . "','" . $jsonPreSlotIds . "'," . time() . ")"
            . " ON DUPLICATE KEY UPDATE data=VALUES(data),update_time=VALUES(update_time)";
        $bolRes = $this->dbutil->query($sql);
        return $bolRes;
    }

    public function getPreAdSlot($arrParams) {
        $arrSelect = [
            'select' => 'data', 
            'where' => "app_id='" . $arrParams['app_id'] . "'",
        ];
        $arrRes = $this->dbutil->getPreadslot($arrSelect);
        if (empty($arrRes[0]['data'])) {
            return [];
        }
        return json_decode($arrRes[0]['data'], true);
    }

}
