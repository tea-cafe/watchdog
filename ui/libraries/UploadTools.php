<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 账户信息 
 */

class UploadTools {

    const KEY_IMG_URL_SALT = 'Qspjv5$E@Vkj7fZb';

    public function __construct() {
        parent::__construct();
        $this->CI->config->load('upload');
        $this->CI->load->helper(['form', 'url']);
    }

    public function index() {
        $this->CI->load->view('upload_form', ['error' => '']);
    }

	/**
     *
	 */
	public function upload($arrUdpConf) {
        $arrUdpConf['upload_path'] = $arrUdpConf['upload_path'] . date("Y/m") . '/';

        if (!is_dir($arrUdpConf['upload_path'])) {
            @mkdir($arrUdpConf['upload_path'], 0777);
        }

        $this->CI->load->library('upload', $arrUdpConf);

        if (!$this->CI->upload->do_upload('file')) {
            return '';
        }
        $arrRes = $this->CI->upload->data();

        $strImgUrl = str_replace(WEBROOT, '', $arrRes['full_path']);
        return $strImgUrl;
    }
}
