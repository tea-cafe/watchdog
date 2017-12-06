<?php
/**
 * 自定义Controller基类
 */
class BG_Controller extends CI_Controller {
    
    public $arrUser = [];

    public function __construct() {
        parent::__construct();
		$this->load->model('UserManager');
        $this->arrUser = $this->UserManager->checkLogin();
    }


    /**
     * json 输出
     *
     * @param $array
     * @bool $bolJsonpSwitch
     */
    protected function outJson($arrData, $intErrCode, $strErrMsg=null,$bolJsonpSwitch = false) {
        $arrData = ErrCode::format($arrData, $intErrCode, $strErrMsg);
        echo json_encode($arrData); 
    } 

}
