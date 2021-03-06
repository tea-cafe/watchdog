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
     * 这里会触发更新 data_for_sdk 更新
     */
    public function modifyProportionAndStyle() {
        if (empty($this->arrUser)) {
            return $this->outJson('', ErrCode::ERR_NOT_LOGIN);
        }
        $arrPostParams = json_decode(file_get_contents('php://input'), true);

        // app_id_map
        $arrAppIdMap = [];
        $strAppSecret = '';
        $intAppIdMapValidMark = 0;
        foreach ($arrPostParams as $key => &$val) {
            if(!in_array($key, self::VALID_PARAMS_KEY)) {
                return $this->outJson('', ErrCode::ERR_INVALID_PARAMS);
            }
            $val = $this->security->xss_clean($val);
            if (in_array($key, array_keys(self::APP_ID_MAP))) {
                if (empty($val)) {
                    continue;
                }
                $intAppIdMapValidMark += 1;
                if (in_array('7', $arrPostParams['default_valid_style']) !== false
                    && $key === 'app_id_map_ta') {
                    $arrTmp = explode('|', $val);
                    if (empty($arrTmp[1])) {
                        return $this->outJson('', ErrCode::ERR_INVALID_PARAMS, '推啊互动API需要填写appID和appSecret，如39322|3WmvuKh4LUEdJ8GknKgRNY9Jvp3NaeWYhxae5vN');
                    }
                    $arrAppIdMap[self::APP_ID_MAP[$key]] = $arrTmp[0];
                    $strAppSecret = $arrTmp[1];
                    continue;
                 }
                $arrAppIdMap[self::APP_ID_MAP[$key]] = $val;
            }
        }

        if ($intAppIdMapValidMark === 0) {
            return $this->outJson('', ErrCode::ERR_INVALID_PARAMS, '提交失败,按样式填写对应上游平台的app id');
        }
        $strAppId = $arrPostParams['app_id'];

        // default_valid_style
        $strValidStyle = '';
        if (empty($arrPostParams['default_valid_style'])) {
            return $this->outJson('', ErrCode::ERR_INVALID_PARAMS, '样式不能为空');
        }
        foreach ($arrPostParams['default_valid_style'] as $styleId) {

            $strValidStyle .= $styleId . ',';
        }
        $strValidStyle = substr($strValidStyle, 0, -1);

        // 分成比例
        $intProportion = intval($arrPostParams['proportion']);
        $this->load->library('DbUtil');
        $arrUpdate = [
            'default_valid_style'   => $strValidStyle,
            'proportion'            => $intProportion,
            'app_id_map'            => json_encode($arrAppIdMap),
            'app_secret'            => $strAppSecret,
            'where'                 => "app_id='" . $strAppId . "'",
        ];
        $arrRes = $this->dbutil->udpMedia($arrUpdate);
        if ($arrRes === false
            || $arrRes['code'] !== 0) {
            $this->outJson('', ErrCode::ERR_SYSTEM, '数据库更新失败');
        }

        return $this->outJson($arrPostParams, ErrCode::OK, '媒体合法广告位样式更新成功');
    }
}
