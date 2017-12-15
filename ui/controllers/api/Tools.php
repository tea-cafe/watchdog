<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 账户信息 
 */

class Tools extends BG_Controller {

    const KEY_IMG_URL_SALT = 'Qspjv5$E@Vkj7fZb';

    const VALID_UPLOAD_SUFFIX = [
        'txt',
        'csv',
        'apk',
        'ipa',
    ];

    public function __construct() {
        parent::__construct();
        $this->load->library('UploadTools');
    }

    /**
     * 仅限运营使用
     */
    public function upload() {
        if (empty($this->arrUser)) {
            return $this->outJson('', ErrCode::ERR_NOT_LOGIN, '会话已过期,请重新登录');
        } 

        // 这里upload 参数接受不走 php input
        $strAppId = $this->input->post('app_id', true);
        if (empty($strAppId)) {
            return $this->outJson('', ErrCode::ERR_INVALID_PARAMS); 
        }
        // 用户白名单过滤

        $arrTmp = explode('.', $_FILES['file']['name']);
        $suffix = $arrTmp[count($arrTmp)-1];
        if (!in_array($suffix, self::VALID_UPLOAD_SUFFIX)) {
            return $this->outJson('', ErrCode::ERR_UPLOAD, '文件类型非法,请重新选择');
        }
        if ($suffix === 'apk'
            || $suffix === 'txt') {
            if ($suffix === 'apk') {
                $suffix = 'app';
            }
            $arrUdpConf = $this->config->item($suffix);
            $arrUdpConf['file_name'] = md5($strAppId . $_FILES['file']['name']);
            $strUrl = $this->uploadtools->upload($arrUdpConf);

            if (empty($strUrl)) {
                return $this->outJson('', ErrCode::ERR_UPLOAD, '上传失败，请重试');
            }
            return $this->outJson(
                ['url' => $strUrl],
                ErrCode::OK,
                '文件上传成功'
            );
        }

    }
}

