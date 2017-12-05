<?php
class BgAdSlot extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->library('DbUtil');
    }

    /**
     * @param array $arrParams
     * @return bool
     */
    public function getPreSlotId($strAppId) {
        $arrSelect = [
            'select' => 'data',
            'where' => "app_id='" . $strAppId . "'",
        ];
        $arrRes = $this->dbutil->getPreadslot($arrSelect);
        if (empty($arrRes[0]['data'])) {
            return [];
        }
        return json_decode($arrRes[0]['data'], true);
    }

    /**
     *
     */
    public function updatePreSlotId($jsonPreSlotId) {
        // check jsonPreSlotId legal
        if ($this->checkPreSlotIdLegal($jsonPreSlotId)) {
            ErrCode::$msg = 'jsonPreSlotId struct check failed';
            return false
        }

        // 回写 pre_slotid
        $arrUpdate = [
            'data' => json_encode($arrPreSlotIds, JSON_UNESCAPED_UNICODE),
            'where' => "app_id='" . $strAppId . "'",
        ];
        $arrRes = $this->dbutil->udpPreadslot($arrUpdate);
        if (!$arrRes
            || $arrRes['code'] !== 0) {
            return [];
        }
        return json_decode($jsonPreSlotId, true);
    }

    /**
     *
     */
    private function checkPreSlotIdLegal($jsonPreSlotId)  {
       return true; 
    }

}
