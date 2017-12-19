<?php
class Jsapi extends MY_Controller {
    public function __construct() {
        parent::__construct();
    }

    public function mediajs() {
        $appKey = 'xxxxxxx';
        $adslotId = 'yyyyyyy';
        $arrParams = $this->input->get(NULL, true);
        if(count($arrParams) != 2 
            || !isset($arrParams['appKey'])
            || !isset($arrParams['adslotId'])) {
            return $this->outJson([], ErrCode::ERR_INVALID_PARAMS);
        }
        $strJs = '';
        $strJs .= "(function(){"."\n";
        $strJs .= "var o = document.getElementsByTagName(\"script\");"."\n";
        $strJs .= "var c = o[o.length-1].parentNode;";
        $strJs .= "var ta = document.createElement('script'); ta.type = 'text/javascript'; ta.async = true;";
        $strJs .= "ta.src = '//yun.lvehaisen.com/h5/media/media-3.2.1.min.js';";
        $strJs .= "ta.onload = function() {";
        $strJs .= "new TuiaMedia({";
        $strJs .= "container: c,";
        $strJs .= "appKey: '".$arrParams['appKey']."',";
        $strJs .= "adslotId: '".$arrParams['adslotId']."'";
        $strJs .= "});";
        $strJs .= "}";
        $strJs .= "var s = document.querySelector('head'); s.appendChild(ta);";
        $strJs .= "})();";
        echo $strJs;

    }
}
