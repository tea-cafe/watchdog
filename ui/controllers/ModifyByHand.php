<?php
/**
 * all target: watchdog/ui/config/modify.php
 * @usage: 以修改 display_strategy 为例, 执行:
 * /usr/local/src/php/bin/php /home/work/watchdog/web/index.php ModifyByHand execute "display_strategy" 
 */
defined('BASEPATH') OR exit('No direct script access allowed');
class ModifyByHand extends CI_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function execute($target) {
        if (!is_cli()
            || empty($target)) {
            exit('no target cmd');
        }
        $this->load->config('modify');
        $arrScriptsList = $this->config->item('modify');
        if (!in_array($target, $arrScriptsList)) {
            exit('task not regist in watchdog/ui/config/modify.php'); 
        }
        $arr = explode('_', $target);
        $model = '';
        foreach ($arr as $k => $v) {
            $model .= ucfirst($v);
        }
        $this->load->model('modify_by_hand/' . $model);    
        $this->$model->do_execute();
    }

}
