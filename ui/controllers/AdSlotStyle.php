<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 后台 广告位样式 管理控制器 
 */

class AdSlotStyle extends BG_Controller {

    const VALID_SLOT_TYPE = [
        'sdk', 'api', 'h5', 'public_sign',
    ];

    const VALID_ADSLOT_KEY = [
        'slot_type',
        'slot_style',
        'img'
    ];

    public function __construct() {
        parent::__construct();
    }

	/**
     *
	 */
	public function testInsertSlotStyle() {
        $arrPostParams = $this->input->post();
        
        foreach($arrPostParams as $key => $val) {

        }

        $sql = 'insert into adslot_style_info(slot_type,slot_style,size,img) values';
        foreach(self::VALID_SLOT_TYPE as $v) {
            $sql .= "('" . $v . "'," . "'banner','300*250','http://asdfasdfasdfasdf.jpg'),"; 
        }
        
        $sql = substr($sql, 0, -1);
        $this->load->model('bg/AdSlotManager');
        $this->AdSlotManager->insertAdSlotStyle($sql);

          
    }
}
