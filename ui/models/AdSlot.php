<?php
class AdSlot extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->library('DbUtil');
    }

    /**
     *
     */
    public function getUpstreamSlotId($intSlotId) {
        $arrSelect = [
            'select' => 'upstream_slot_id',
            'where' => 'slot_id=' . $intSlotId,
        ];
        $arrRes = $this->dbutil->getAdslotmap($arrSelect);
        if (empty($arrRes)) {
            return '';
        }
        return $arrRes[0]['upstream_slot_id'];

    }

    /**
     *
     */
    public function getAdSlotList($strAccountId, $pn = 1, $rn = 10, $strSlotName = '') {
        $arrSelect = [
            'select' => 'count(*) as total',
            'where' => "account_id='" . $strAccountId . "'",
            'order_by' => 'slot_style,update_time desc',
        ];
        if (!empty($strSlotName)) {
            $arrSelect['where'] .= " AND slot_name like '%" . $strSlotName . "%'";
        }
        $arrRes = $this->dbutil->getAdSlot($arrSelect);
        if (empty($arrRes)) {
            $intCount = 0;
            $arrRes = [];
            return [
                'list' => $arrRes,
                'pagination' => [
                    'total' => $intCount,
                    'pageSize' => $rn,
                    'current' => $pn,
                ],
            ];
        }
        $intCount = $arrRes[0]['total'];
        $arrSelect = [
            'select' => 'slot_id,app_id,media_name,media_platform,slot_name,slot_style,slot_size,switch,create_time',
            'where' => "account_id='" . $strAccountId . "'",
            'order_by' => 'create_time DESC',
            'limit' => $rn*($pn-1) . ',' . $rn,
        ];
        if (!empty($strSlotName)) {
            $arrSelect['where'] .= " AND slot_name like '%" . $strSlotName . "%'";
        }
        $arrRes = $this->dbutil->getAdSlot($arrSelect);
        if (!empty($arrRes[0])) {
            $this->config->load('style2platform_map');
            $arrStyleMap = $this->config->item('style2platform_map');
            foreach ($arrRes as &$arrSlot) {
                foreach ($arrStyleMap[$arrSlot['slot_style']] as $key => $val) {
                    if ($key === 'des') {
                        continue;
                    }
                    $arrSlot['slot_size'] = $val['size'][$arrSlot['slot_size']];
                    break;
                }
                $arrSlot['slot_style'] = $arrStyleMap[$arrSlot['slot_style']]['des'];
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
     * @param $strSlotType
     * @return array
     */
    public function getAllSlotTypeList($strSlotType) {
        $arrSelect = [
            'select' => 'slot_style,img,size',
            'where' => "slot_frozen_status=0 AND slot_type='" . $strSlotType . "'",
        ];
        $arrRes = $this->dbutil->getAdslotstyle($arrSelect);
        return $arrRes;
    }

    /**
     * @param array $arrParams
     * @return array
     */
    public function addAdSlotInfo($arrParams) {
        $this->load->model('adslot/InsertAdslot');
        // 检验媒体是否过审
        $arrAppIdMap = $this->InsertAdslot->checkMediaLigal($arrParams);
        if (empty($arrAppIdMap)) {
            return false;
        }

        // 生成本站媒体的slot_id
        $arrParams['slot_id'] = $this->dbutil->getAutoincrementId('adslot');

        // 有多少个slot_style的上游，就从从预生成的slotid中分配几个和本站的slot_id对应，并插如映射记录到映射表
        $arrPreSlotIds = $this->InsertAdslot->getPreSlotid($arrParams['app_id']);
        if (empty($arrPreSlotIds)) {
            return false;
        }

        $bolRes = $this->InsertAdslot->distributePreSlotId(
            $arrPreSlotIds,
            intval($arrParams['slot_style']),
            intval($arrParams['slot_size']),
            $arrParams['app_id'],
            $arrParams['account_id'],
            $arrParams['slot_id'],
            $arrAppIdMap,
            $arrParams
        );
        return $bolRes;
    }

}
