<?php
class PreAdSlotManager extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->library('DbUtil');
    }

    public function insertPreAdSlot($arrParams) {
        $arrRes = $this->dbutil->setPreadslot($arrParams);
    }

    public function getPreAdSlot($arrParams) {
        $arrSelect = [
            'select' => 'data', 
            'where' => "app_id='" . $arrParams['app_id'] . "'",
        ];
        $arrRes = $this->dbutil->getPreadslot($arrSelect);
       var_dump($arrRes);exit; 
    }

}
