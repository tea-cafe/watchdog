<?php
/**
 * media 信息
 */
class MediaInfo extends MY_Controller {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 获取媒体信息
     */
    public function index() {
        if (empty($this->arrUser)) {
            return $this->outJson('', ErrCode::ERR_NOT_LOGIN);
        }

        $app_id = $this->input->get('app_id', true);
        $this->load->model('Media');
        $arrRes = $this->Media->getMediaInfo($app_id);
        if ($arrRes) {
            return $this->outJson($arrRes, ErrCode::OK, '');
        }
        return $this->outJson('', ErrCode::ERR_SYSTEM);
    }
}

