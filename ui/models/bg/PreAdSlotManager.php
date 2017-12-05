<?php
class PreAdSlotManager extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function insertPreAdSlot($arrParams) {
        $this->load->library('DbUtil');
        $arrRes = $this->dbutil->setPreadslot($arrParams);
    }

}
