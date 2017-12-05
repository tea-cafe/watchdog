<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Demo 
 */

class Demo extends MY_Controller {

    public function __construct() {
        parent::__construct();
    }

	/**
	 */
	public function index()
	{
        $this->load->model('Media');
        $arrData = $this->Media->getMediaLists();

        // databases 
        //$this->load->database();
        //$objRes = $this->db->query('show tables');
        //var_dump($objRes->result());
        
        // redis
        $this->load->library('RedisUtil');
        $this->redisutil->set('name', 'songzishuo');
        $this->redisutil->expire('name', 30);
        $name = $this->redisutil->get('name');
        $this->load->library('Smartylib');
        $this->smartylib->assign('name', $name);
        $this->smartylib->display('demo.tpl');

	}
}
