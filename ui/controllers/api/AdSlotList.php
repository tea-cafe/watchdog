<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 媒体列表
 */

class AdSlotList extends MY_Controller {

    public function __construct() {
        parent::__construct();
    }

	/**
	 */
	public function index()
	{
        $slot_name = $this->input->get('slot_name');
        $pn = empty($this->input->get('currentPage')) ? 1 : intval($this->input->get('currentPage'));
        $rn = empty($this->input->get('pageSize')) ? 10 : intval($this->input->get('pageSize'));
        if (empty($this->arrUser)) {
            return $this->outJson('', ErrCode::ERR_NOT_LOGIN);
        }
        $this->load->model('AdSlot');
        $arrData = $this->AdSlot->getAdSlotList($this->arrUser['account_id'], $pn, $rn, $slot_name);

        $this->outJson($arrData, ErrCode::OK);
	}

}
