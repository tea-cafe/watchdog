<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 后台 预生成slot_id管理 控制器 
 */

class PreAdSlot extends BG_Controller {

    const VALID_PRESLOT_KEY = [
        "app_id",
        "slot_style",
        "slot_size",
        "ad_upstream",
        "pre_slod_ids",
    ];

	/**
     *
	 */
	public function index() {
        $arrPostParams = json_decode(file_get_contents('php://input'), true);
        if (empty($this->arrUser)) {
            return $this->outJson('', ErrCode::ERR_NOT_LOGIN);
        }
        foreach ($arrPostParams as $key => &$val) {
            if(!in_array($key, self::VALID_PRESLOT_KEY)
                || empty($val)) {
                return $this->outJson('', ErrCode::ERR_INVALID_PARAMS); 
            }
            $val = $this->security->xss_clean($val);
        }
        
        $this->load->model('bg/PreAdSlotManager');
        $arrPreAdSlotBefor = $this->PreAdSlotManager->getPreAdSlot($arrPostParams);
         

        $arrPreAdSlotAfter = [];

        var_dump($arrPostData);exit;
          
    }
}
