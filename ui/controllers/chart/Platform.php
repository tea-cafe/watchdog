<?php
class Platform extends MY_Controller {
    public function __construct() {
        parent::__construct();
    }

    public function getList() {
        $this->load->model('chart/Platforms');
        $arrList = $this->Platforms->getList();
        return $arrList?$this->outJson($arrList, ErrCode::OK) : $this->outJson([], ErrCode::OK);
    }
}
