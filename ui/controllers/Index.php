<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Index extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('User');
    }

    public function _remap($method, $params = []) {
        if (isset($_GET['logout'])) {
            return $this->logout();
        }
        if (isset($_GET['login'])) {
            return $this->login();
        }

        /*
        if (method_exists($this, $method)) {
            $reflection = new ReflectionMethod($this, $method);
            if ($reflection->isProtected()) {
                $this->page = $this->page ? $this->page : 'index_h5';
                return $this->$method($params);
            }
        }
         */

        $this->index();
    }

	/**
     *
	 */
	public function index()
	{
        $arrData = [];
        $arrData['login'] = $this->User->checkLogin();
        if ($arrData['login']) {
            var_dump($arrData['login'], '已登录');exit;
            head("Location:  /", 302, true); // 跳转到媒体数据页
        }
        echo '这是未登录 默认介绍页';
        // index 提示页
	}

	/**
     *
	 */
    public function login() {
        echo '这是login 页面';
        // 显示登录表单
    }

	/**
     *
	 */
    public function logout() {
        $this->User->clearLoginInfo();
        $this->index();
        return;
    }
}
