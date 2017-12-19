<?php
/**
 * media修改接口
 */
class MediaModify extends MY_Controller {

    const VALID_MEDIA_KEY = [
        'app_id',
        'media_name',
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

    public function __construct() {
        parent::__construct();
    }

    /**
     * 媒体信息修改
     */
    public function index() {//{{{//
        if (empty($this->arrUser)) {
            return $this->outJson('', ErrCode::ERR_NOT_LOGIN);
        }

        $arrPostParams = json_decode(file_get_contents('php://input'), true);
        // TODO 各种号码格式校验
        foreach ($arrPostParams as $key => &$val) {
            if(!in_array($key, self::VALID_MEDIA_KEY)) {
                return $this->outJson('', ErrCode::ERR_INVALID_PARAMS); 
            }
            $val = $this->security->xss_clean($val);
        }

        if (empty($arrPostParams['app_id'])
            || empty($arrPostParams['media_name'])
            || !is_array($arrPostParams['industry'])
            || empty($arrPostParams['media_delivery_method'])
            || empty($arrPostParams['media_platform'])) {
            return $this->outJson('', ErrCode::ERR_INVALID_PARAMS); 
        }

        // 检测用户状态，如果不是评审未通过(check_status=4) 拒绝修改
        $this->load->model('Media');
        $arrRes = $this->Media->getMediaInfo($arrPostParams['app_id']);
        if (empty($arrRes)
            || $arrRes['check_status'] != 4) {
            return $this->outJson('', ErrCode::ERR_SYSTEM, '用户状态检查非法,禁止修改');
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

        $arrPostParams['where'] = "app_id='" . $arrPostParams['app_id'] . "'";
        unset($arrPostParams['app_id']);
        $bolRes = $this->Media->updateMediaInfo($arrPostParams);
        if ($bolRes) {
            return $this->outJson('', ErrCode::OK, '媒体信息修改成功');
        }
        return $this->outJson('', ErrCode::ERR_SYSTEM);
    }//}}}//

}
