<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 账户信息 
 */

class Tools extends BG_Controller {

    const KEY_IMG_URL_SALT = 'Qspjv5$E@Vkj7fZb';

    public function __construct() {
        parent::__construct();
        $this->load->library('UploadTools');
    }

    /**
     *
     */
    public function uploadImg() {
        $this->load->model('User');
        $arrUser = $this->User->checkLogin();
        if (empty($arrUser)) {
            return $this->outJson('', ErrCode::ERR_NOT_LOGIN, '会话已过期,请重新登录');
        } 

        $arrUdpImgConf = $this->config->item('img');
        $arrUdpImgConf['file_name'] = md5($arrUser['account_id'] . self::KEY_IMG_URL_SALT . $_FILES['id']);

        $strUrl = $this->uploadtools->uploadImg($arrUdpImgConf);
        if (empty($strUrl)) {
            return $this->outJson('', ErrCode::ERR_UPLOAD, '上传csv文件失败，请重试');
        }
        return $this->outJson(
            ['url' => $strUrl],
            ErrCode::OK,
            '图片上传成功');
    }

    /**
     * upload csv
     */
    public function uploadCsv() {
        $this->load->model('User');
        $arrUser = $this->User->checkLogin();
        if (empty($arrUser)) {
            return $this->outJson('', ErrCode::ERR_NOT_LOGIN, '会话已过期,请重新登录');
        } 
        $arrUdpCsvConf = $this->config->item('csv');
        $strUrl = $this->uploadtools->upload($arrUdpCsvConf);
        if (empty($strUrl)) {
            return $this->outJson('', ErrCode::ERR_UPLOAD, '上传csv文件失败，请重试');
        }
        return $this->outJson(
            ['url' => $strUrl],
            ErrCode::OK,
            '文件上传成功');
    }

    /**
     * upload app
     */
    public function uploadApp() {
        if (empty($this->arrUser)) {
            return $this->outJson('', ErrCode::ERR_NOT_LOGIN, '会话已过期,请重新登录');
        } 

        $arrPostParams = json_decode(file_get_contents('php://input'), true);
        $strAppId = $arrPostParams['app_id'];
        if (empty($strAppId)) {
            return $this->outJson('', ErrCode::ERR_INVALID_PARAMS); 
        }
        // 用户白名单过滤

        $arrUdpAppConf = $this->config->item('app');
        $arrUdpAppConf['file_name'] = md5($strAppId . $_FILES['file']['name']);
        $strUrl = $this->uploadtools->upload($arrUdpAppConf);
        if (empty($strUrl)) {
            return $this->outJson('', ErrCode::ERR_UPLOAD, '上传csv文件失败，请重试');
        }
        return $this->outJson(
            ['url' => $strUrl],
            ErrCode::OK,
            '文件上传成功');
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

