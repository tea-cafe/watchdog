<?php
class AdSlot extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->library('DbUtil');
    }

    /**
     *
     */
    public function getAdSlotLists($intAccountId, $pn = 1, $rn = 10, $intCount = 0, $strSlotName = '') {
        $this->load->library('DbUtil');
        if ($intCount === 0) {
            $arrSelect = [
                'select' => 'count(*) as total',
                'where' => 'account_id=' . $intAccountId,
            ];
            $arrRes = $this->dbutil->getAdSlot($arrSelect);
            $intCount = $arrRes[0]['total'];
        }
        $arrSelect = [
            'select' => 'slot_id,app_id,media_name,media_platform,slot_name,slot_type,slot_style,slot_size,switch,create_time',
            'where' => 'account_id=' . $intAccountId,
            'order_by' => 'create_time DESC',
            'limit' => $rn*($pn-1) . ',' . $rn,
        ];
        if (!empty($condition)) {
            $arrSelect['where'] .= " AND media_name like '%" . $strSlotName . "%'"; 
        }
        $arrRes = $this->dbutil->getAdSlot($arrSelect);
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
            'select' => 'slot_style_id,slot_style,img,size',
            'where' => "slot_frozen_status=0 AND slot_type='" . $strSlotType . "'",
        ];
        $arrRes = $this->dbutil->getAdslotstyle($arrSelect);  
        return $arrRes;
    }

    /**
     * @param array $arrParams
     * @return array
     */
    public function insertAdSlotInfo($arrParams) {
        $this->load->model('adslot/InsertAdslot');
        // 检验媒体是否过审
        $bolRes = $this->InsertAdslot->checkMediaLigal($arrParams);
        if (!$bolRes) {
            // todo
            echo 'checkMediaLigal false';exit; 
            return false;
        }

        // 生成媒体的slot_id
        $arrParams['slot_id'] = $this->dbutil->getAutoincrementId('adslot');

        // 从预生成的slotid中为此slotid 分配， 并插如映射记录到映射表
        $arrPreSlotIds = $this->InsertAdslot->getPreSlotid($arrParams['app_id']); 
        if (empty($arrPreSlotIds)) {
            echo 'getPreSlotid false';exit;
            return false;
        }

        $arrSlotIdsForApp = $this->InsertAdslot->distributePreSlotId(
            $arrPreSlotIds,
            $arrParams['slot_type'],
            $arrParams['slot_style'],
            $arrParams['slot_size'], 
            $arrParams['app_id']
        );
        if (empty($arrSlotIdsForApp)) {
            return false; 
        }
        
        // 更新slot_id map
        $bolRes = $this->InsertAdslot->insertSlotMap(
            $arrSlotIdsForApp, 
            $arrParams['account_id'], 
            $arrParams['slot_id'],
            $arrParams['app_id']
        );
        if (!$bolRes) {
            return false;
        }

        // 插入adslot_info
        $arrRes = $this->dbutil->setAdslot($arrParams);
        if (!$arrRes
            || $arrRes['code'] !== 0) {
            return false;
        }
        return true;
    }

}
