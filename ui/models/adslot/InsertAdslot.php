<?php
class InsertAdslot extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->library('DbUtil');
    }
    
    /**
     * step 1: check Media 信息是否已过审
     */
    public function checkMediaLigal($arrParams) {
        $arrCheckMediaLegal = [
            'select' => 'app_id',
            'where' => "app_id='" . $arrParams['app_id'] . "' AND media_name='" . $arrParams['media_name'] . "' AND check_status=2",
            'limit' => '0,1',
        ];
        $arrRes = $this->dbutil->getMedia($arrCheckMediaLegal);
        if (empty($arrRes[0])) {
            log_message('error', 'adslot regist step 1: Media info illege');
            return false;
        }
        return true;
    }


    /**
     * step 2: 获取运营为此media预分配的slot_id列表，
     *
     */
    public function getPreSlotId($strAppId) {
        $arrSelect = [
            'select' => 'data',
            'where' => "app_id='" . $strAppId . "'",
        ];
        $arrRes = $this->dbutil->getPreadslot($arrSelect);
        if (empty($arrRes[0]['data'])) {
            log_message('error', 'adslot regist step 2 : pre_slot list for ' . $strAppId . 'is empty');
            echo 'getPreSlotid false';exit;
            return false;
        }
        return json_decode($arrRes[0]['data'], true);
    }


    /**
     * step 3: 为此媒体分配预生成的广告位id.从与分配的slot_id列表按渠道分别选出一个可用的上游slot_id，标记已占用，然后回写预分配的slot_id列表。最终返回此mediaslot_id对应的每个上游的slot_id列表.
     * @param array $arrPreSlotIds 预分配slot_id数据
     * @param string $strSlotType
     * @param string $strSlotStyle
     * @param string $strSlotSize
     * @param string $strAppId
     * @return array 分配的上游slot_id列表
     */
    public function distributePreSlotId($arrPreSlotIds, $strSlotType, $strSlotStyle, $strSlotSize, $strAppId) {
        $arrSlotIdsForApp = [];
        foreach($arrPreSlotIds as $upstream => &$arrType){
            $arrTmp = $arrType[$strSlotType][$strSlotStyle][$strSlotSize];
            foreach ($arrTmp as $slotid => $used) {
                if ($used === 0) {
                    $arrSlotIdsForApp[] = [
                        'upstream' => $upstream,
                        'upstream_slot_id' => $slotid,
                    ];
                    $arrTmp[$slotid] = 1;
                    break;
                }
            }
            $arrType[$strSlotType][$strSlotStyle][$strSlotSize] = $arrTmp;
        }

        if (empty($arrSlotIdsForApp)) {
            ErrCode::$msg = '广告位申请超出限制，请联系工作人员';

            log_message('error', 'adslot regist step 3 : can not get a slot_id can be used for ' . $strAppId);
            return [];
        }

        // 回写 pre_slotid
        $arrUpdate = [
            'data' => json_encode($arrPreSlotIds, JSON_UNESCAPED_UNICODE),
            'where' => "app_id='" . $strAppId . "'",
        ];
        $arrRes = $this->dbutil->udpPreadslot($arrUpdate);
        if (!$arrRes
            || $arrRes['code'] !== 0) {
            echo 'huixie pre_slotid shibai';exit;
            log_message('error', 'adslot regist step 3 : preslot write back failed ' . $strAppId);
            return [];
        }
        return $arrSlotIdsForApp;
    }

    /**
     * step 4: 插入 slot_map记录
     *
     */
    public function insertSlotMap($arrSlotIdsForApp, $intAccountId, $intSlotId, $strAppId) { 
        $sql = 'INSERT INTO adslot_map(slot_id,account_id,app_id,upstream,upstream_slot_id,create_time,update_time) VALUES';
        foreach($arrSlotIdsForApp as $val) {
            $sql .= "(" . $intAccountId 
                . "," . $intSlotId 
                . ",'" . $strAppId 
                . "','" . $val['upstream'] 
                . "','" . $val['upstream_slot_id'] 
                . "'," . time() 
                . "," . time() . "),";
        }
        $sql = substr($sql, 0, -1);
        $bolRes = $this->dbutil->query($sql);
        if (!$bolRes) {
            echo 'insertSlotMap shibai';exit;
            log_message('error', 'adslot regist step 4 : insert slot map failed for ' . $strAppId);
            
        }
        return $bolRes;
    }


}
