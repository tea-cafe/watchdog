<?php
class Platforms extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->library('DbUtil');
    }

    public function getList() {
        $arrSelect = [
            'select' => '*',
        ];
        $arrRes = $this->dbutil->getPlatform($arrSelect);
        if(empty($arrRes[0])) {
            return false;
        }

        return $arrRes;
    }

}
