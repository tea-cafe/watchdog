<?php
/**
 * 编辑在拿到媒体信息，去上游申请媒体认证通过后，继续为此媒体申请不同类型下的slot_id，通过后，手动选择哪种类型的slot_id 可用，然后更新 media_info的valid_style_ids
 *
 */
class MediaValidSlotIds extends MY_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        if (empty($this->arrUser)) {
            return $this->outJson('', ErrCode::ERR_NOT_LOGIN);
        }
        $strAppId = $this->input->post('app_id', true);
        $strValidSlotIds = $this->input->post('valid_ids', true);
        $this->load->model('Media');
        $arrUpdate = [
            'valid_slot_ids' => $strValidSlotIds,
            'where' => "app_id=.'" . $strAppId . "'",
        ]
        $bolRes = $this->Media->udpMediaInfo($arrUpdate);
        if ($bolRes) {
            return $this->outJson('', ErrCode::OK, '媒体合法广告位样式更新成功');
        }
    }


}
