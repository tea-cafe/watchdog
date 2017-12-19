<?php
/**
 * 编辑在拿到媒体信息，去上游申请媒体认证通过后，继续为此媒体申请不同类型下的slot_id，通过后，手动选择哪种类型的slot_id 可用，然后更新 media_info的valid_style_ids
 *
 */
class MediaValidStyle extends MY_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        if (empty($this->arrUser)) {
            return $this->outJson('', ErrCode::ERR_NOT_LOGIN);
        }
        $arrPostParams = json_decode(file_get_contents('php://input'), true);
        $strAppId = $arrPostParams['app_id'];
        $strValidStyle = $arrPostParams['default_valid_style'];
        $intProportion = intval($arrPostParams['proportion']);
        $this->load->library('DbUtil');
        $arrUpdate = [
            'default_valid_style' => $strValidStyle,
            'proportion' => $intProportion,
            'where' => "app_id='" . $strAppId . "'",
        ];
        $arrRes = $this->dbutil->udpMedia($arrUpdate);
        if ($arrRes['code'] === 0) {
            return $this->outJson('', ErrCode::OK, '媒体合法广告位样式更新成功');
        }
        $this->outJson('', ErrCode::ERR_SYSTEM, '数据库更新失败');
    }


}
