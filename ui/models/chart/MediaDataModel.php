<?php
class MediaDataModel extends CI_Model {
    public function __construct() {
        parent::__construct();
        $this->load->library('DbUtil');
    }

    public function getMediaSumDataList($arrParams) {
        $arrSelect = [
            'select' => '*',
        ];
        $arrRes = $this->dbutil->getallPlatform($arrSelect);
        if(empty($arrRes[0])) {
            return false;
        }

        return $arrRes;
    }

    public function getMediaDailyDataList($arrParams) {
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
