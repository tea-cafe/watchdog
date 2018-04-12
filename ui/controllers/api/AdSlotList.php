<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 媒体广告位列表
 */

class AdSlotList extends BG_Controller {

    public function __construct() {
        parent::__construct();
    }

	/**
	 */
	public function index()
	{
        $strAppId = $this->input->get('app_id', true);
        $strSlotName = $this->input->get('slot_name', true);
        $pn = empty($this->input->get('currentPage')) ? 1 : intval($this->input->get('currentPage'));
        $rn = empty($this->input->get('pageSize')) ? 10 : intval($this->input->get('pageSize'));
        if (empty($this->arrUser)) {
            return $this->outJson('', ErrCode::ERR_NOT_LOGIN);
        }
        $this->load->model('AdSlotManager');
        $arrData = $this->AdSlotManager->getAdSlotLists($strAppId, $pn, $rn, $strSlotName);

        $this->outJson($arrData, ErrCode::OK);
	}

}
