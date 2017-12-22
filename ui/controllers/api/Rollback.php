<?php
class Rollback extends BG_Controller {
    public function __construct() {
        parent::__construct();
    }

    public function accountBalance() {
        if (empty($this->arrUser)) {
            return $this->outJson('', ErrCode::ERR_NOT_LOGIN);
        }

        $this->load->model('scripts/RollbackChannelBalance');
        $arrRes = $this->RollbackChannelBalance->do_execute();
        if ($arrRes['code'] === 0) {
            return $this->outJson('', ErrCode::OK, $arrRes['message']);
        }
        $this->outJson('', ErrCode::ERR_SYSTEM, $arrRes['message']);
    }

    public function monthlyBill() {
        if (empty($this->arrUser)) {
            return $this->outJson('', ErrCode::ERR_NOT_LOGIN);
        }
        $this->load->model('scripts/RollbackMonthlyBill');
        $arrRes = $this->RollbackMonthlyBill->do_execute();
        if ($arrRes['code'] === 0) {
            return $this->outJson('', ErrCode::OK, $arrRes['message']);
        }
        $this->outJson('', ErrCode::ERR_SYSTEM, $arrRes['message']);
    }
}
