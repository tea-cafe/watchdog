<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 月账单合入余额
 */
class GenerateChannelBalance extends BG_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        if (empty($this->arrUser)) {
            return $this->outJson('', ErrCode::ERR_NOT_LOGIN);
        } 

        $this->load->model('scripts/GenerateChannelBalance');
        $arrRes = $this->GenerateChannelBalance->do_execute();
        if ($arrRes['code'] === 0) {
            return $this->outJson('', ErrCode::OK, $arrRes['message']);
        }
        $this->outJson('', ErrCode::ERR_SYSTEM, $arrRes['message']);
    }

}

