<?php
// all tasks: watchdog/ui/config/scripts.php
defined('BASEPATH') OR exit('No direct script access allowed');
class Crontab extends CI_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function execute($task) {
        if (!is_cli()
            || empty($task)) {
            exit('no target task');
        }
        $this->load->config('scripts');
        $arrScriptsList = $this->config->item('scripts');
        if (!in_array($task, $arrScriptsList)) {
            exit('task not regist in watchdog/ui/config/scripts.php'); 
        }
        $this->load->model('scripts/' . $task);    
        $arrRes = $this->$task->do_execute();
        var_dump($arrRes);
    }

}
