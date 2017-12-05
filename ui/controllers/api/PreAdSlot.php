<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 后台 预生成slot_id管理 控制器 
 */

class PreAdSlot extends MY_Controller {

    const VALID_ADSLOT_KEY = [
        'app_id',
        'data',
    ];

    public function __construct() {
        parent::__construct();
    }

	/**
     *
	 */
	public function testInsertPreAdSlot() {
        $arrPostParams = $this->input->post();
        
        foreach($arrPostParams as $key => $val) {

        }

        include_once('/home/work/anteater/ui/testPreSlotid.php');
        $jsonData = json_encode($data);

        $arrPostParams['app_id'] = 'ad45cd3fa59ea7dfca549f22b16821ef';
        $arrPostParams['data'] = $jsonData;
        $this->load->model('bg/PreAdSlotManager');
        $this->PreAdSlotManager->insertPreAdSlot($arrPostParams);

          
    }
}
