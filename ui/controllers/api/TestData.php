<?php

class TestData extends MY_Controller{
    public function __construct(){
        parent::__construct();
    }

    public function daily(){
        $time = 1509465600;

        exit;
        $this->load->library('DbUtil');
        for($a = 0;$a <= 30;$a++){
            $data['time'] = $time + $a * 86400;
            $data['account_id'] = 3;
            $data['app_id'] = 'd428b23';
            $data['media_name'] = '嘻嘻哈哈';
            $data['media_platform'] = 'H5';
            $data['money'] = mt_rand(1000,9999).mt_rand(0,99);

            //var_dump($data);
            $this->dbutil->setDaily($data);
            unset($data);
        }

    }
    
    public function Monthly(){
        $this->load->library('DbUtil');
        $a = array(
            'd228b23' => '雷锋军事',
            'd328b23' => '3G门户',
            'd428b23' => '嘻嘻哈哈',
        );

        $accid = array(
            0 => '0463451394dc5dfc634f9463d6b12791',
            1 => '04fb2125927e6194f0628cd6ebf300b8',
            2 => '8bfb5685eda20aa40c4c05047c0fd2e6',
            3 => '929777a8e8e647b702c800f2c40578d3',
            4 => 'c595943825a7ca8b4777d186befc49cb',
        );

        foreach($accid as $k1 => $v1){
            foreach($a as $k =>$v){
                $data['time'] = 1509465600;
                $data['account_id'] = $v1;
                $data['app_id'] = $k;
                $data['media_name'] = $v;
                $data['media_platform'] = 'H5';
                $data['money'] = mt_rand(10000,99999).mt_rand(10,99);
                $this->dbutil->setMonthly($data);
                unset($data);
            }   
        }
    }

} 
?>
