<?php
/**
 * media 信息
 */
class GenerateCode extends MY_Controller {

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
        $slot_id = $this->input->get('slot_id', true);
        $this->load->model('Media');
        $arrRes = $this->Media->getAppSecretAndAppIdMap($app_id);
        if (!empty($arrRes['app_secret'])
            && !empty($arrRes['app_id_map'])) {
            $arrAppIdMap = json_decode($arrRes['app_id_map'], true);
            $this->load->model('AdSlot');
            return $this->outJson(
                [
                    'app_secret' => $arrRes['app_secret'],
                    'app_id' => isset($arrAppIdMap['TUIA']) ? $arrAppIdMap['TUIA'] : '',
                    'slot_id' => $this->AdSlot->getUpstreamSlotId($slot_id),
                ], 
                ErrCode::OK, '');
        }
        return $this->outJson('', ErrCode::ERR_SYSTEM, '媒体信息有误');
    }
}

