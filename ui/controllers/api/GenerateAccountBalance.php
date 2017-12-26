<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 月账单合入余额
 */
class GenerateAccountBalance extends BG_Controller {

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

    public function getPreAccountBalanceList() {
        $pn = isset($_GET['currentPage']) ? intval($this->input->get('currentPage', true)) : 1;
        $rn = isset($_GET['pageSize']) ? intval($this->input->get('pageSize', true)) : 10;
        $this->load->model('AccountBalanceManager');
        $arrRes = $this->AccountBalanceManager->getPreAccountBalanceList($pn, $rn);
        if (empty($arrRes)) {
            return $this->outJson('', ErrCode::ERR_SYSTEM, ErrCode::$msg);
        }
        $this->outJson($arrRes, ErrCode::OK);
    }

}

