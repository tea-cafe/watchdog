<?php
class ApiSession extends MY_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function gen_session() {
		$url = 'https://engine.lvehaisen.com/index/activity?appKey=%s&adslotId=%s&md=%s&timestamp=%s&nonce=%s&signature=%s';
        $arrParams = $this->input->get(NULL, true);
        if(count($arrParams) != 4 
            || !isset($arrParams['appKey']) 
            || !isset($arrParams['adslotId'])
            || !isset($arrParams['appSecret'])
            || !isset($arrParams['hb'])) {
            return $this->outJson([], ErrCode::ERR_INVALID_PARAMS);
        }
		$appKey = $arrParams['appKey'];
		$adslotId = $arrParams['adslotId'];
        $appSecret = $arrParams['appSecret'];
        $_md = base64_decode($arrParams['hb']);
        if(is_null(json_decode($_md))) {
            return $this->outJson([], ErrCode::ERR_INVALID_PARAMS);
        }
        $md = base64_encode(gzencode($_md, 9));
        $timestamp = time();
        $nonce = rand(pow(10, 5), pow(10, 6)-1);
        $strHash = "appSecret={$appSecret}&md={$md}&nonce={$nonce}&timestamp={$timestamp}";
		$signature = sha1($strHash, false);
        $api_url = sprintf($url, $appKey, $adslotId, urlencode($md), $timestamp, $nonce, $signature);
        $this->load->library('Curl');
        $this->curl->create($api_url);
        $str = $this->curl->execute();
        $Headers =  $this->curl->info['url'];
        if($Headers) {
            header('Location:'.$Headers, true, 302);
            return;
        }
        return $this->outJson([], ErrCode::ERR_INVALID_PARAMS);
    }
}
