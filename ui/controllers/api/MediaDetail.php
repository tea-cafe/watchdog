<?php
class MediaDetail extends BG_Controller {
	public function __construct(){
		parent::__construct();
	}

    public function index() {
        if (empty($this->arrUser)) {
            return $this->outJson('', ErrCode::ERR_NOT_LOGIN); 
        }
        $strAppId = $this->input->get('app_id', true); 
        if (empty($strAppId)) {
            return $this->outJson('', ErrCode::ERR_INVALID_PARAMS, 'app_id is empty');
        }
        $this->load->model('MediaManager');
        $arrData = $this->MediaManager->getMediaDetail($strAppId);
        $this->outJson($arrData, ErrCode::OK);
    }
}
