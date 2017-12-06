<?php
class MediaPreSlotId extends BG_Controller {
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
        $this->load->model('AdSlot');
        $arrData = $this->BgAdSlot->getPreSlotId($strAppId);
        $this->outJson($arrData, ErrCode::OK);
    }

    /**
     *
     *
     */
    public function modify() {
        $jsonPostParams = file_get_contents('php://input');
        $this->load->model('bg/BgAdSlot');
        $arrData = $this->BgAdSlot->updatePreSlotId($jsonPostParams);
        if (empty($arrData)) {
            return $this->outJson($arrData, ErrCode::ERR_SYSTEM);
        }
        return $this->outJson($arrData, ErrCode::OK);
    }
}
