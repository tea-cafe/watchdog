<?php
class MediaList extends BG_Controller {
	public function __construct(){
		parent::__construct();
	}

    public function index() {
        if (empty($this->arrUser)) {
            return $this->outJson('', ErrCode::ERR_NOT_LOGIN); 
        }
        $media_name = $this->input->get('media_name', true);
        $account_id = $this->input->get('account_id', true);
        $check_status = $this->input->get('check_status', true);
        $pn = empty($this->input->get('currentPage')) ? 1 : intval($this->input->get('currentPage'));
        $rn = empty($this->input->get('pageSize')) ? 10 : intval($this->input->get('pageSize'));
        $this->load->model('MediaManager');
        $arrData = $this->MediaManager->getMediaList($pn, $rn, $account_id, $check_status, $media_name);
        $this->outJson($arrData, ErrCode::OK);
    }

}
