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
        $strAppId = $this->input->get('app_id');
        $pn = intval($this->input->get('currentPage'));
        $rn = intval($this->input->get('pageSize'));
        $total = intval($this->input->get('total'));
        if (empty($this->arrUser)) {
            return $this->outJson('', ErrCode::ERR_NOT_LOGIN);
        }
        $this->load->model('AdSlotManager');
        $arrData = $this->AdSlotManager->getAdSlotLists($strAppId, $pn, $rn, $total);

        $this->outJson($arrData, ErrCode::OK);
	}

}
