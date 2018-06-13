<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 更新slot_id后端对应的上游slot_id信息
 */

class AdSlotUpdate extends BG_Controller {

    public function __construct() {
        parent::__construct();
    }

	/**
     *
	 */
	public function index()
	{
        if (empty($this->arrUser)) {
            return $this->outJson('', ErrCode::ERR_NOT_LOGIN);
        }
        $arrPostParams = json_decode(file_get_contents('php://input'), true);
        if (empty($arrPostParams['app_id'])
            || empty($arrPostParams['slot_id'])
            || empty($arrPostParams['upstream_ids'])) {
            return $this->outJson('', ErrCode::ERR_INVALID_PARAMS);
        }
        $this->load->model('AdSlotManager');
        $bol = $this->AdSlotManager->updateUpstreamSlotId(
            $arrPostParams['app_id'], 
            $arrPostParams['slot_id'], 
            $arrPostParams['upstream_ids'] 
        );
        if ($bol) {
            $this->outJson(['success' => 1], ErrCode::OK);
        } else {
            $this->outJson(['failed' => 1], ErrCode::ERR_SYSTEM);
        }
	}

}
