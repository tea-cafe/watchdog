<?php
// 后台触发： 策略更新(更新所有记录)、上游增加(更新一条记录)
// 前台触发： 广告位申请(更新一条记录)

class SyncSdkMediaInfo extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->library('DbUtil');
    }
    
    /**
     * 用户有新注册slot id时触发
     */
    public function syncWhenAdSlotIdRegist($app_id, $slot_id, $slot_style, $arrUpstreamSlotIdsForApp) {
        if (empty($app_id)
            || empty($slot_style)
            || empty($arrUpstreamSlotIdsForApp)) {
            log_message('error', 'sync data_for_sdk: syncWhenAdSlotIdRegist params error');
            return [];
        }
        // 1 读表 adslot_style_info的display_strategy字段 查策略
        $arrSelect = [
            'select' => 'display_strategy',
            'where' => "slot_style=" . $slot_style,
        ];
        $arrRes = $this->dbutil->getAdslotstyle($arrSelect);
        if (empty($arrRes[0])
            || empty($arrRes[0]['display_strategy'])) {
            return [];
        }
        $arrSlotStrategy = json_decode($arrRes[0]['display_strategy'], true);

        // 2 读表media_info的app_id_map字段，获取app_id s上下游对应关系
        $arrSelect = [
            'select' => 'app_id_map',
            'where' => "app_id='" . $app_id . "'",
        ];
        $arrRes = $this->dbutil->getMedia($arrSelect);
        if (empty($arrRes[0])
            || empty($arrRes[0]['app_id_map'])) {
            return [];
        }
        $arrAppIdMap = json_decode($arrRes[0]['app_id_map'], true);

        $arrTmp = [];
        foreach ($arrUpstreamSlotIdsForApp as $arrUpstreamSlotid) {
            // 根据app_id_map 过滤display_strategy中未过审的上游
            if (in_array($arrUpstreamSlotid['upstream'], array_keys($arrSlotStrategy))) {
                $arrTmp['strategy'][$arrUpstreamSlotid['upstream']] = $arrSlotStrategy[$arrUpstreamSlotid['upstream']];     
            }
            $arrTmp['map'][$arrUpstreamSlotid['upstream']]['app_id'] = $arrAppIdMap[$arrUpstreamSlotid['upstream']]; 
            $arrTmp['map'][$arrUpstreamSlotid['upstream']]['slot_id'] = $arrUpstreamSlotid['upstream_slot_id']; 
        }

        // 3 获取之前的 data_for_sdk表中此媒体的信息
        $arrSelcet = [
            'select' => 'app_id,data',
            'where' => "app_id='" . $app_id . "'",
        ];
        $arrDataForSdk = $this->dbutil->getSdkData($arrSelcet);
        if ($arrDataForSdk === false) {
            return false;
        }
        if (!empty($arrDataForSdk)
            && !empty($arrDataForSdk[0]['data'])) {
            $arrSdkDataBefore = json_decode($arrDataForSdk[0]['data'], true); 
        }
        if (empty($arrSdkDataBefore)) {
            $arrSdkDataBefore = [];
        }
        $arrSdkDataAfter = $arrSdkDataBefore;
    
        $arrSdkDataAfter[$slot_id] = $arrTmp;

        $sql = 'INSERT INTO data_for_sdk(app_id,data,update_time) VALUES(\'' . $app_id . "','" . json_encode($arrSdkDataAfter) . "'," . time() . ') ON DUPLICATE KEY UPDATE data=VALUES(data),update_time=VALUES(update_time)';
        $bolRes = $this->dbutil->query($sql);
        if ($bolRes ===  false) {
            log_message('error', 'update data_for_sdk app_id=[' . $app_id . '] failed');
        }
        return $bolRes;
    }

    private function getSdkMediaInfo($strAppId) {
        $arrSelect = [
            'select' => 'data',
            'where' => "app_id='" . $strAppId . "'",
        ];
        $arrRes = $this->dbutil->getSdkData($arrSelect);
    } 

}
