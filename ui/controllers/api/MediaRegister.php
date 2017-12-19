<?php
/**
 * media注册接口
 */
class MediaRegister extends MY_Controller {

    const VALID_MEDIA_KEY = [
        'media_platform',
        'app_detail_url',
        'app_package_name',
        'media_keywords',
        'media_desc',
        'url',
        'app_platform',
        'industry',
        'media_delivery_method',
    ];

    const VALID_MEDIA_VARIFY_KEY = [
        'app_id',
        'media_platform',
        'app_package_name',
        'app_download_url',
        'h5_app_key',
    ];

    public function __construct() {
        parent::__construct();
    }

    /**
     * 基本信息注册
     */
    public function index() {//{{{//
        if (empty($this->arrUser)) {
            return $this->outJson('', ErrCode::ERR_NOT_LOGIN);
        }

        $arrPostParams = json_decode(file_get_contents('php://input'), true);

        if (empty($arrPostParams['media_platform'])
            || empty($arrPostParams['media_name'])
            || !is_array($arrPostParams['industry'])
            || empty($arrPostParams['media_delivery_method'])
            || empty($arrPostParams['media_platform'])) {
            return $this->outJson('', ErrCode::ERR_INVALID_PARAMS); 
        }

        foreach ($arrPostParams as $key => &$val) {
            $val = $this->security->xss_clean($val);
        }

        if ($arrPostParams['media_platform'] === 'iOS'
            || $arrPostParams['media_platform'] === 'Android') {
            $this->config->load('app_platform');
            $arrPlatformList = $this->config->item('app_platform');
            if (empty($arrPlatformList[$arrPostParams['app_platform']])) {
                return $this->outJson('', ErrCode::ERR_SYSTEM, 'app platfrom 出错'); 
            }
            // 投放方式
            if ($arrPostParams['media_delivery_method'] === 'SDK') {
                $arrPostParams['default_valid_style'] = '1,2,3,4,6';
            } else {
                $arrPostParams['media_delivery_method'] === 'API';
                $arrPostParams['default_valid_style'] = '7';
            }

        }
        if ($arrPostParams['media_platform'] === 'H5') {
            $arrPostParams['default_valid_style'] = '9,10,11,12,13,14';
            $arrPostParams['media_delivery_method'] = 'JS';
        } 

        $arrPostParams['check_status'] = 1;

        if (is_array($arrPostParams['industry'])) {
            $strIndustry = '';
            foreach ($arrPostParams['industry'] as $v) {
                $strIndustry .= $v . '-';
            }
            $strIndustry = substr($strIndustry, 0, -1);
        }
        $arrPostParams['industry'] = $strIndustry;

        $arrPostParams['account_id'] = $this->arrUser['account_id'];
        $this->load->model('Media');
        $bolRes = $this->Media->insertMediaInfo($arrPostParams);
        if ($bolRes) {
            return $this->outJson('', ErrCode::OK, '媒体注册成功');
        }
        return $this->outJson('', ErrCode::ERR_SYSTEM);
    }//}}}//

    /**
     * 上传包，或者appkey 之后，置状态为1 待验证
     */
    public function verifyStatus() {
        if (empty($this->arrUser)) {
            return $this->outJson('', ErrCode::ERR_NOT_LOGIN);
        }

        $arrPostParams = json_decode(file_get_contents('php://input'), true);
        if (empty($arrPostParams['media_platform'])) {
            return $this->outJson('', ErrCode::ERR_INVALID_PARAMS);
        }

        // TODO 各种号码格式校验
        foreach ($arrPostParams as $key => &$val) {
            if(!in_array($key, self::VALID_MEDIA_VARIFY_KEY)) {
                return $this->outJson('', ErrCode::ERR_INVALID_PARAMS);
            }
            $val = $this->security->xss_clean($val);
        }

        unset($arrPostParams['app_id']);
        $this->load->model('Media');

        $arrUpdate['where'] = 'account_id=' . $this->arrUser['account_id'] . " AND app_id='" . $arrPostParams['app_id'] . "'";
        $bolRes = $this->Media->updateMediaInfo($arrPostParams);
        if ($bolRes) {
            return $this->outJson('', ErrCode::OK, '提交验证信息成功');
        }
        return $this->outJson('', ErrCode::ERR_SYSTEM);
    }//}}}//


    /*
     *
     */
    public function industryList() {
         $this->load->config('trade');
         $arrTrade = json_decode($this->config->item('trade'), true);
    }

}
