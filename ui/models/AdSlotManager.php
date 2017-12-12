<?php
class AdSlotManager extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->library('DbUtil');
    }

    /**
     *
     */
    public function getAdSlotLists($strAppId, $pn = 1, $rn = 10) {
        $this->load->library('DbUtil');
        $arrSelect = [
            'select' => 'count(*) as total',
            'where' => "app_id='" . $strAppId . "'",
        ];
        $arrRes = $this->dbutil->getAdSlot($arrSelect);
        $intCount = $arrRes[0]['total'];
        $arrSelect = [
            'select' => 'slot_id,app_id,media_name,media_platform,slot_name,slot_style,slot_size,upstream_adslots,,switch,create_time',
            'where' => "app_id='" . $strAppId . "'",
            'order_by' => 'create_time DESC',
            'limit' => $rn*($pn-1) . ',' . $rn,
        ];
        if (!empty($condition)) {
            $arrSelect['where'] .= " AND media_name like '%" . $strSlotName . "%'"; 
        }
        $arrRes = $this->dbutil->getAdSlot($arrSelect);
        if ($arrRes) {
            $this->config->load('style2platform_map');
            $arrStyleMap = $this->config->item('style2platform_map');
            foreach ($arrRes as &$val) {
                foreach ($arrStyleMap[$val['slot_style']] as $k => $v) {
                    if ($k !== 'des') {
                        $val['slot_size'] = $v['size'][$val['slot_size']];
                        break;
                    }  
                }
                $val['slot_style'] = $arrStyleMap[$val['slot_style']]['des'];
                $val['upstream_adslots'] = json_decode($val['upstream_adslots'], true);
            }
        }
        return [
            'list' => $arrRes,
            'pagination' => [
                'total' => $intCount,
                'pageSize' => $rn,
                'current' => $pn,
            ],
        ];

    }


    /**
     * @param array $arrParams
     * @return bool
     */
    public function getPreSlotId($strAppId) {
        $arrSelect = [
            'select' => 'data',
            'where' => "app_id='" . $strAppId . "'",
        ];
        $arrRes = $this->dbutil->getPreadslot($arrSelect);
        if (empty($arrRes[0]['data'])) {
            return [];
        }
        return json_decode($arrRes[0]['data'], true);
    }

    /**
     *
     */
    public function updatePreSlotId($jsonPreSlotId) {
        // check jsonPreSlotId legal
        if ($this->checkPreSlotIdLegal($jsonPreSlotId)) {
            ErrCode::$msg = 'jsonPreSlotId struct check failed';
            return false;
        }

        // 回写 pre_slotid
        $arrUpdate = [
            'data' => json_encode($arrPreSlotIds, JSON_UNESCAPED_UNICODE),
            'where' => "app_id='" . $strAppId . "'",
        ];
        $arrRes = $this->dbutil->udpPreadslot($arrUpdate);
        if (!$arrRes
            || $arrRes['code'] !== 0) {
            return [];
        }
        return json_decode($jsonPreSlotId, true);
    }

    /**
     *
     */
    private function checkPreSlotIdLegal($jsonPreSlotId)  {
        // TODO
       return true; 
    }

}
