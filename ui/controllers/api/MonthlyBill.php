<?php
class MonthlyBill extends BG_Controller {
	public function __construct(){
		parent::__construct();
	}

    public function index() {
        if (empty($this->arrUser)) {
            return $this->outJson('', ErrCode::ERR_NOT_LOGIN); 
        }
        $account_id = $this->input->get('account_id', true);
        if (empty($account_id)) {
            return $this->outJson('', ErrCode::ERR_INVALID_PARAMS);
        }
        $pn = empty($this->input->get('currentPage')) ? 1 : intval($this->input->get('currentPage'));
        $rn = empty($this->input->get('pageSize')) ? 10 : intval($this->input->get('pageSize'));
        $this->load->model('BillManager');
        $arrData = $this->BillManager->getAppBillList($account_id, $pn, $rn);
        $this->outJson($arrData, ErrCode::OK);
    }

}
