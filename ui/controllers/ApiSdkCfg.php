<?php
class ApiSdkCfg extends MY_Controller {
    public function __construct() {
        parent::__construct();
    }

    /**
     * @param void
     * @return void
     */
    public function getSdkCfg() {
        $arrParams = $this->input->get(NULL, true);
        if(count($arrParams) != 2 
            || !isset($arrParams['app_id'])
            || !isset($arrParams['slot_id'])) {
            return $this->outJson([], ErrCode::ERR_INVALID_PARAMS);
        }
        $out = '{
        "code": 0,
            "msg": "OK",
            "data": [
        {
            "2039": {
                "strategy": {
                    "bai": 50,
                    "gdt": 20,
                    "vid": 0
                },
                "maps": {
                    "bai": {
                        "appid": "bef",
                        "posid": "bef"
                    },
                    "gdt": {
                        "appid": "bef",
                        "posid": "bef"
                    },
                    "vid": {
                        "appid": "bef",
                        "posid": "bef"
                    }
                }
            }
        },
        {
            "2040": {
                "strategy": {
                    "bai": 50,
                    "gdt": 20,
                    "vid": 0
                },
                "maps": {
                    "bai": {
                        "appid": "bef",
                        "posid": "bef"
                    },
                    "gdt": {
                        "appid": "bef",
                        "posid": "bef"
                    },
                    "vid": {
                        "appid": "bef",
                        "posid": "bef"
                    }
                }
            }
        }
    ]
    }';
        echo $out;
        return;
    }
}
