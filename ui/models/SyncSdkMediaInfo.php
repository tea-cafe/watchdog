<?php
// 后台触发： 展示策略(display_stretage) 或者 上游增加(app_id_map)
// 前台触发： 广告位申请(更新一条记录)

class SyncSdkMediaInfo extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->library('DbUtil');
    }

    /**
     * 后台更新display_strategy时触发
     * 只需要更新 display_stratgy
     * 查到所有此slot_style(id)的app_id,遍历更新插入
     * $display_strategy = [
     *  'BAIDU' => 50,
     *  'GDT' => 30,
     *
     * ]
     */
    public function syncWhenDisplayStratgeChange($slot_style, $display_stratgy) {
        if (empty($slot_style)
            || empty($display_stratgy)
            || !is_array($display_stratgy)) {
            log_message('error', 'sync data_for_sdk: syncWhenDisplayStratgeChange params error');
            return [];
        }

        $intFinalRes = 0;  
        // 1 读表 adslot_info
        $arrSelect = [
            'select' => 'distinct(app_id) as app_id',
            'where' => "slot_style=" . $slot_style,
        ];
        $arrRes = $this->dbutil->getAdSlot($arrSelect);
        if (empty($arrRes[0])
            || empty($arrRes[0]['app_id'])) {
            return [];
        }

        $intCount = count($arrRes);
        //每次更新1000条
        $intUpdateNumOnce = 1000;
        for ($i = 0; $i <= intval($intCount/$intUpdateNumOnce); $i++) {
            $strWhereIn = '(';
            for ($j=0; $j<$intUpdateNumOnce; $j++) {
                if (isset($arrRes[$i*$intUpdateNumOnce+$j])
                    && !empty($arrRes[$i*$intUpdateNumOnce+$j]['app_id'])) {
                    $strWhereIn .= "'" . $arrRes[$i*$intUpdateNumOnce+$j]['app_id'] . "',"; 
                }
            }
            $strWhereIn = substr($strWhereIn, 0, -1) . '';
            $arrSelectAppId2SlotIdMap = [
                'select' => 'slot_id,app_id',
                'where' => 'app_id IN ' . strWhereIn, 
            ];
            $arrAppIdSlotId = $this->dbutil->getAdslot($arrSelectAppId2SlotIdMap);
            if (empty($arrAppIdSlotId)
                || empty($arrAppIdSlotId[0]['slot_id'])) {
                continue;
            }
            $arrAppId2SlotIdMap = [];
            foreach ($arrAppIdSlotId as $arrAppIdSlotId) {
                $arrAppIdSlotId[$arrAppIdSlotId['app_id']][] = $arrAppIdSlotId['slot_id'];
            }
            unset($arrAppIdSlotId);

            // 查出之前的数据
            $arrSelectDataForSdk = [
                'select' => 'app_id,data',
                'where' => "app_id IN " . $strWhereIn, 
            ];
            $arrDataForSdk = $this->dbutil->getSdkData($rrSelectDataForSdk);
            if (empty($arrDataForSdk)
                || empty($arrDataForSdk[0]['data'])) {
                continue;
            }

            // 更新strategy字段, 生成回写语句
            $sqlFinal = 'INSERT INTO data_for_sdk(app_id,data) VALUES(';
            foreach ($arrDataForSdk as &$arrAppIdData) {
                /*
                    $arrAppId = [
                        $slot_id1 => [
                            'strategy' => [
                                'BAIDU' => 50,
                                'GDT' => 30,
                            ], 
                            'map' => [
                                ''''
                            ],
                        ],
                        $slot_id2 => ......
                    ]
                 */
                if (empty($arrAppIdData['data'])
                    || empty($arrAppId2SlotIdMap[$arrAppIdData['app_id']])) {
                    continue;
                }
                $arrData = json_decode($arrAppIdData['data'], true);
                if (empty($arrData)
                    || !is_array($arrData)) {
                    continue; 
                }
                foreach ($arrAppId2SlotIdMap[$arrAppIdData['app_id']] as $slotId) {
                    if (isset($arrData[$slotId])) {
                        $arrData[$slotId]['strategy'] = $display_stratgy; 
                    } 
                }
                $arrAppIdData['data'] = json_encode($arrData); 
                
                $sqlFinal .= "('" . $arrAppIdData['app_id'] . "','"
                        . $arrAppIdData['data'] . "'),"; 
            }
            $sqlFinal = substr($sqlFinal, 0, -1);
            $sqlFinal .= ' ON DUPLICATE KEY UPDATE data=VALUES(data)'; 
            $bolRes = $this->dbutil->query($sqlFinal);
            $intFinalRes += 1;
            if ($bolRes === false) {
                ErrCode::$msg = 'SyncSdkMediaInfo执行有' . $intFinalRes . '次失败， 请重试';
                return false;
            }
        }
        return true;
    }
}
