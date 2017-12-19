<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 媒体列表
 */

class MediaList extends MY_Controller {

    public function __construct() {
        parent::__construct();
    }

	/**
     * 账号所有的媒体列表
	 */
	public function index()
	{
        if (empty($this->arrUser)) {
            return $this->outJson('', ErrCode::ERR_NOT_LOGIN);
        }
        $condition = $this->input->get('media_name', true);
        $checkstatus = $this->input->get('check_status', true);
        $pn = empty($this->input->get('currentPage')) ? 1 : intval($this->input->get('currentPage'));
        $rn = empty($this->input->get('pageSize')) ? 10 : intval($this->input->get('pageSize'));
        $total = intval($this->input->get('total'));
        $this->load->model('Media');
        $arrData = $this->Media->getMediaList($this->arrUser['account_id'], $pn, $rn, $total, $condition, $checkstatus);
        $this->outJson($arrData, ErrCode::OK);
	}


    /**
     * 获取已经过审的媒体列表
     */
    public function getPassedList() {
        if (empty($this->arrUser)) {
            return $this->outJson('', ErrCode::ERR_NOT_LOGIN);
        }
        $this->load->model('Media');
        $arrData = $this->Media->getPassedMediaList($this->arrUser['account_id']);
        $this->outJson($arrData, ErrCode::OK);
    }

}
