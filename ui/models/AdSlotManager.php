<?php
class AdSlotManager extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->library('DbUtil');
    }

    /**
     * 更新广告位对应上游id信息
     * @param string $strAppId
     * @param int $intSlotStyle
     * @param array $arrUpstreamIds
     * @return bool
     */
    public function updateUpstreamSlotId($strAppId, $intSlotId, $arrUpstreamIds) {//{{{//

        $arrAdSlotInfo = $this->getAdSlotInfo($intSlotId);
        if (empty($arrAdSlotInfo)
            || $arrAdSlotInfo['app_id'] != $strAppId
            || empty($arrAdSlotInfo['upstream_adslots'])) {
            ErrCode::$msg = '广告位信息有误';
            return false;
        }
        $intStyle = $arrAdSlotInfo['slot_style'];
        $intSize = $arrAdSlotInfo['slot_size'];
        $arrUpstreamIds = [];
        $arrTmp = json_decode($arrAdSlotInfo['upstream_adslots'], true);
        if (empty($arrTmp)) {
            ErrCode::$msg = '广告位信息参数有误';
            return false;
        }

        foreach ($arrTmp as $val) {
            $arrUpstreamIds[$val['upstream']] = $val['upstream_slot_id'];
        }

        $arrPreSlotIds = $this->getPreSlotId($strAppId);
        if (count($arrPreSlotIds) <= count($arrUpstreamIds)) {
            ErrCode::$msg = '广告位无需更新，请检查';
            return false;
        }
        $arrAddUpstream = [];
        $intTmp = 0;
        foreach ($arrPreSlotIds as $strUpstream => &$arrStyleSize) {
            if (isset($arrUpstreamIds[$strUpstream])) {
                continue;
            }
            if (empty($arrStyleSize)
                || empty($arrStyleSize[$intStyle])
                || empty($arrStyleSize[$intStyle][$intSize])) {
                ErrCode::$msg = "没有可用y样式($intStyle, $intSize)的预生成广告位id，请检查";
                continue;
            }
            foreach ($arrStyleSize[$intStyle][$intSize] as $id => $mark) {
                if ($mark === 1) {
                    continue;
                } 
                $arrAddUpstream[$strUpstream]['slot_id'] = $id;
                $arrStyleSize[$intStyle][$intSize][$id] = 1;
                $intTmp += 1;
                break;
            }
        }

        if ($intTmp === 0) {
            ErrCode::$msg = '无可用预生成广告位，请检查';
            return false;
        }

        // 获取修改前的data_for_sdk中此广告位的data
        $arrSdkData = $this->getDataForSdkData($strAppId);
        if (empty($arrSdkData)) {
                ErrCode::$msg = '查询相关信息失败，请重试'; 
                return false;
        }
        $arrAppIdMap = $this->getUpstreamAppId($strAppId);
        foreach ($arrAddUpstream as $ups => &$val) {
            $val['app_id'] = $arrAppIdMap[$ups];
        }
        $arrSdkData[$intSlotId]['strategy'] = $this->getDisplayStrategy($intStyle); 
        $arrSdkData[$intSlotId]['map'] += $arrAddUpstream;
        $arrDataAdSlot = [];
        foreach ($arrSdkData[$intSlotId]['map'] as $up => $v) {
            $arrDataAdSlot[] = [
                'upstream' => $up,
                'upstream_slot_id' => $v['slot_id'],
            ]; 
        }

        //echo json_encode($arrSdkData);exit;
        $bol = $this->updateDb(
            $arrAdSlotInfo['account_id'], 
            $strAppId, 
            $intSlotId, 
            $arrSdkData, 
            $arrPreSlotIds, 
            $arrDataAdSlot, 
            $arrAddUpstream
        );
        if ($bol) {
            ErrCode::$msg .= ' 更新失败，请重试!';
        }
        return $bol;
    }//}}}//

    /**
     * 查询adslot_info
     * $param int $intSlotId
     * @return array
     */
    public function getAdSlotInfo($intSlotId) {
        $arrSelect = [
            'select' => 'account_id,app_id,slot_style,slot_size,upstream_adslots',
            'where' => 'slot_id=' . $intSlotId,
        ];
        $arrRes = $this->dbutil->getAdSlot($arrSelect);
        if (empty($arrRes[0])) {
            return [];
        }
        return $arrRes[0];
    }



    /**
     * 更新data_for_sdk
     * @param string $strAppId
     * @param array $arrData
     * @return bool
     */
    private function updateDb($strAccountId, $strAppId, $intSlotId, $arrDataForSdk, $arrDataForPreAdSlot, $arrDataForAdSlotInfo, $arrAddUpstream) {
        // save data_for_sdk
        $strSqlUpdateDataForSdk = "UPDATE data_for_sdk SET data='" . json_encode($arrDataForSdk) . "' WHERE app_id='" . $strAppId . "'";
        // save pre_adslot
        $strSqlUpdatePreAdSlot = "UPDATE pre_adslot SET data='" . json_encode($arrDataForPreAdSlot) . "' WHERE app_id='" . $strAppId . "'";
        // save adslot_info
        $strSqlUpdateAdSlotInfo = "UPDATE adslot_info SET upstream_adslots='" . json_encode($arrDataForAdSlotInfo) . "' WHERE slot_id=" . $intSlotId;

        // save adslot_map
        $strSqlInsertAdSlotMap = "INSERT INTO adslot_map(account_id,slot_id,app_id,ad_upstream,upstream_slot_id,create_time,update_time) VALUES"; 
        foreach ($arrAddUpstream as $up => $val) {
            $strSqlInsertAdSlotMap .= "('" . $strAccountId . "'," . $intSlotId . ",'" . $strAppId . "','" . $up . "','" . $val['slot_id'] . "'," . time() . "," . time() . "),";
        }
        $strSqlInsertAdSlotMap = substr($strSqlInsertAdSlotMap, 0, -1);

        // 事务 start
        $this->db->trans_begin();
        $this->db->query($strSqlUpdateDataForSdk);
        if ($this->db->affected_rows() === 0) {
            $this->db->trans_rollback();
            ErrCode::$msg = 'data_for_sdk 未更新，请检查是否需要更新';
            return false; 
        }
        $this->db->query($strSqlUpdatePreAdSlot);
        if ($this->db->affected_rows() === 0) {
            $this->db->trans_rollback();
            ErrCode::$msg = 'pre_adslot 未更新，请检查是否需要更新';
            return false; 
        }
        $this->db->query($strSqlUpdateAdSlotInfo);
        if ($this->db->affected_rows() === 0) {
            $this->db->trans_rollback();
            ErrCode::$msg = 'adslot_Info 未更新，请检查是否需要更新';
            return false;
        }
        $this->db->query($strSqlInsertAdSlotMap);
        if ($this->db->affected_rows() === 0) {
            $this->db->trans_rollback();
            ErrCode::$msg = ' 插入 adslot_map 失败';
            return false;
        }
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            ErrCode::$msg = ' 事务执行失败';
            return false;
        } else {
            $this->db->trans_commit();
            return true;
        }
    }

    /**
     * 获取上游app_id
     * @param string $strAppId
     * @return string
     */
    public function getUpstreamAppId($strAppId) {//{{{//

        $arrSelect = [
            'select' => 'app_id_map',
            'where' => "app_id='$strAppId'", 
        ];
        $arrRes = $this->dbutil->getMedia($arrSelect);
        if (empty($arrRes[0])) {
            return [];
        }
        return json_decode($arrRes[0]['app_id_map'], true);
    }//}}}//


    /**
     * 获取data_for_sdk 广告位数据
     * @param string $strAppId
     * @return array
     */
    public function getDataForSdkData($strAppId) {//{{{//
        $arrSelect = [
            'select' => 'data',
            'where' => "app_id='$strAppId'", 
        ];
        $arrRes = $this->dbutil->getSdkData($arrSelect);
        if (empty($arrRes[0])) {
            return [];
        }
        return json_decode($arrRes[0]['data'], true);
        
    }//}}}//

    /**
     * 获取展示策略
     *
     */
    public function getDisplayStrategy($intSlotStyle) {//{{{//
        $arrSelect = [
            'select' => 'display_strategy',
            'where' => 'slot_style=' . $intSlotStyle ,
        ];

        $arrRes = $this->dbutil->getAdslotStyle($arrSelect);
        if (empty($arrRes[0])) {
            return [];
        }
        return json_decode($arrRes[0]['display_strategy'], true);
    }//}}}//

    /**
     * 获取广告位列表
     * @param string $strAppId
     * @param int $pn
     * @param int $rn
     * @param string $strSlotName
     * @return array
     */
    public function getAdSlotLists($strAppId, $pn = 1, $rn = 10, $strSlotName) {//{{{//
        $arrSelect = [
            'select' => 'count(*) as total',
            'where' => "app_id='" . $strAppId . "'",
        ];
        if (!empty($strAlotName)) {
            $arrSelect['where'] .= " AND slot_name like '%" . $strSlotName . "%'";
        }
        $arrRes = $this->dbutil->getAdSlot($arrSelect);
        $intCount = $arrRes[0]['total'];
        $arrSelect = [
            'select' => 'slot_id,app_id,media_name,media_platform,slot_name,slot_style,slot_size,upstream_adslots,,switch,create_time',
            'where' => "app_id='" . $strAppId . "'",
            'order_by' => 'slot_style,update_time DESC',
            'limit' => $rn*($pn-1) . ',' . $rn,
        ];
        if (!empty($strAlotName)) {
            $arrSelect['where'] .= " AND slot_name like '%" . $strSlotName . "%'";
        }
        $arrRes = $this->dbutil->getAdSlot($arrSelect);
        if ($arrRes) {
            // id -> 中文
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

    }//}}}//


    /**
     * @param string $strAppId
     * @return bool
     */
    public function getPreSlotId($strAppId) {//{{{//
        $arrSelect = [
            'select' => 'data',
            'where' => "app_id='" . $strAppId . "'",
        ];
        $arrRes = $this->dbutil->getPreadslot($arrSelect);
        if (empty($arrRes[0]['data'])) {
            ErrCode::$msg = '广告位申请已超限，请联系工作人员';
            return [];
        }
        return json_decode($arrRes[0]['data'], true);
    }//}}}//

    /**
     * @param string $jsonPreSlotId
     * @return array
     */
    public function updatePreSlotId($jsonPreSlotId) {//{{{//
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
    }//}}}//

    /**
     *
     */
    private function checkPreSlotIdLegal($jsonPreSlotId)  {
        // TODO
       return true;
    }

    /**
     * getSlotBySlotId
     */
    public function getSlotBySlotId($strSlotId) {//{{{//
        $arrSelect = [
            'select' => 'slot_name',
            'where' => "slot_id='" . $strSlotId . "'",
        ];

        $arrRes = $this->dbutil->getAdSlot($arrSelect);
        if (empty($arrRes[0])) {
            return false;
        }
        return $arrRes[0];
    }//}}}//


}
