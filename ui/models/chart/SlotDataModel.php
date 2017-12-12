<?php
class SlotDataModel extends CI_Model {
    public function __construct() {
        parent::__construct();
        $this->load->library('DbUtil');
    }

    public function getSlotSumDataList($arrParams) {
        $arrSelect = [
            'select' => '*',
        ];
        $arrRes = $this->dbutil->getallPlatform($arrSelect);
        if(empty($arrRes[0])) {
            return false;
        }

        return $arrRes;
    }

    public function getSlotDailyDataList($arrParams) {
        $arrSelect = [
            'select' => '*',
        ];
        $arrRes = $this->dbutil->getallPlatform($arrSelect);
        if(empty($arrRes[0])) {
            return false;
        }

        return $arrRes;

    }
}
