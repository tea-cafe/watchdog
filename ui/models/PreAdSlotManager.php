<?php
class PreAdSlotManager extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->library('DbUtil');
    }

    /**
     * 所有此媒体未被分配的slot_id列表
     */
    public function getPreSlotIdList($strAppId, $pn = 1, $rn = 10) {//{{{//
        $arrSelect = [
            'select' => 'data',
            'where' => "app_id='" . $strAppId . "'",
        ];

        $arrRes = $this->dbutil->getPreAdSlot($arrSelect);
        $arrPreAdSlotIdsDisplay = [];
        if (!empty($arrRes[0]['data'])) {
            $arrPreAdSlotMap = json_decode($arrRes[0]['data'], true);
            if ($arrPreAdSlotMap) {
                $this->config->load('style2platform_map');
                $arrStyleMap = $this->config->item('style2platform_map');
                foreach ($arrPreAdSlotMap as $strUpstream => $arrStyle) {
                    foreach ($arrStyle as $intStyleId => $arrSize) {
                        foreach ($arrSize as $intSizeId => $arrSlotIds) {
                            if (empty($arrStyleMap[$intStyleId])) {
                                continue;
                            }
                            $strDisStyle = $arrStyleMap[$intStyleId]['des'];
                            $strDisSize = $arrStyleMap[$intStyleId][$strUpstream]['size'][$intSizeId];
                            $strTmp = '';
                            foreach ($arrSlotIds as $slotId => $used) {
                                if ($used === 1) {
                                    unset($arrSlotIds[$slotId]);
                                    continue;
                                }
                                $strTmp .= $slotId . ',';
                            }   
                            $arrPreAdSlotIdsDisplay[] = [
                                'slot_style' => $strDisStyle,
                                'ad_upstream' => $strUpstream,
                                'slot_size' => $strDisSize,
                                'count' => count($arrSlotIds),
                                'list' => substr($strTmp, 0, -1),
                            ];
                        }
                    }

                }
            }

        }
        return [
            'list' => $arrPreAdSlotIdsDisplay,
            'pagination' => [
                'total' => count($arrPreAdSlotIdsDisplay),
                'pageSize' => $rn,
                'current' => $pn,
            ],
        ];
    }//}}}//

    /**
     *
     */
    public function insertPreAdSlot($strAppId, $jsonPreSlotIds) {
        $sql = 'INSERT INTO pre_adslot(app_id,data,update_time) VALUES' 
            . "('" . $strAppId . "','" . $jsonPreSlotIds . "'," . time() . ")"
            . " ON DUPLICATE KEY UPDATE data=VALUES(data),update_time=VALUES(update_time)";
        $bolRes = $this->dbutil->query($sql);
        return $bolRes;
    }

    /**
     *
     */
    public function getPreAdSlot($arrParams) {
        $arrSelect = [
            'select' => 'data', 
            'where' => "app_id='" . $arrParams['app_id'] . "'",
        ];
        $arrRes = $this->dbutil->getPreadslot($arrSelect);
        if (empty($arrRes[0]['data'])) {
            return [];
        }
        return json_decode($arrRes[0]['data'], true);
    }

}
