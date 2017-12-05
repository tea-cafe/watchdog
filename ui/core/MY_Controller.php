<?php
/**
 * 自定义Controller基类
 */
class MY_Controller extends CI_Controller {
    
    public $arrUser = [];

    public function __construct() {
        parent::__construct();
        $this->load->model('User');
        $this->arrUser = $this->User->checkLogin();
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
