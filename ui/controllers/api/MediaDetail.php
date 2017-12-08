<?php
class MediaDetail extends BG_Controller {

    const VALID_PARAMS_KEY = [
        'app_id',
        'proportion',
        'add_id_map',
        'default_valid_style',
        'app_id_map_bd',
        'app_id_map_gdt',
        'app_id_map_ta',
        'app_id_map_yz',
    ];

    const APP_ID_MAP = [
        'app_id_map_bd' => 'BAIDU',
        'app_id_map_gdt' => 'GDT',
        'app_id_map_ta' => 'TUIA',
        'app_id_map_yz' => 'YEZI',
    ];

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
        $this->load->model('MediaManager');
        $arrData = $this->MediaManager->getMediaDetail($strAppId);
        $this->outJson($arrData, ErrCode::OK);
    }

    /**
     * 修改分成比例 & 默认可用样式 & app_id_map
     */
    public function modifyProportionAndStyle() {
        if (empty($this->arrUser)) {
            return $this->outJson('', ErrCode::ERR_NOT_LOGIN);
        }
        $arrPostParams = json_decode(file_get_contents('php://input'), true);
        $arrAppIdMap = [];
        foreach ($arrPostParams as $key => &$val) {
            if(!in_array($key, self::VALID_PARAMS_KEY)) {
                return $this->outJson('', ErrCode::ERR_INVALID_PARAMS);
            }
            $val = $this->security->xss_clean($val);
            if (in_array($key, array_keys(self::APP_ID_MAP))) {
                $arrAppIdMap[] = [
                    self::APP_ID_MAP[$key] => $val,
                ];
            }
        }

        $strAppId = $arrPostParams['app_id'];

        // default_valid_style
        $strValidStyle = '';
        foreach ($arrPostParams['default_valid_style'] as $val) {
            $strValidStyle .= $val . ',';
        }
        $strValidStyle = substr($strValidStyle, 0, -1);

        // app_id_map
        $strAppIdMap = '';
        foreach ($arrPostParams['app_id_map'] as $val) {
            $strAppIdMap .= $val . ',';
        }
        $strAppIdMap = substr($strAppIdMap, 0, -1);

        $intProportion = intval($arrPostParams['proportion']);
        $this->load->library('DbUtil');
        $arrUpdate = [
            'default_valid_style'   => $strValidStyle,
            'app_id_style'          => $strAppIdMap,
            'proportion'            => $intProportion,
            'app_id_map'            => json_encode($arrAppIdMap),
            'where'                 => "app_id='" . $strAppId . "'",
        ];
        $arrRes = $this->dbutil->udpMedia($arrUpdate);
        if ($arrRes['code'] === 0) {
            return $this->outJson($arrPostParams, ErrCode::OK, '媒体合法广告位样式更新成功');
        }
        $this->outJson('', ErrCode::ERR_SYSTEM, '数据库更新失败');
    }
}
