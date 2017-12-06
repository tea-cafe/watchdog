<?php 
/**
* 这是一个smarty的初始化类
*/
require_once(APPPATH.'libraries/smarty/Smarty.class.php');

class Smartylib extends Smarty {

    function __construct()
    {
        parent::__construct();
        $this->setTemplateDir(WEBROOT . '/templates/');
        $this->setCompileDir(WEBROOT . '/templates_c/');
        $this->setConfigDir(WEBROOT . '/configs/');
        $this->setCacheDir(WEBROOT . '/cache/');
        $this->setLeftDelimiter('{%');
        $this->setRightDelimiter('%}');
        if (DEBUG) {
            $this->debugging = true;
            $this->setForceCompile(true);
        } else {
            $this->setForceCompile(false);
            //编译检查关闭,编译服务器会执行编译,打开服务器消耗太大
            $this->setCompileCheck(0);
        }
        //$this->addPluginsDir(WEBROOT . '/plugin/');
    }
}
