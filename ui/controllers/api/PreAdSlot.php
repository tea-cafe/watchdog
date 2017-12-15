<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 后台 预生成slot_id管理 控制器
 */

class PreAdSlot extends BG_Controller {

    const VALID_PRESLOT_KEY = [
        "app_id",
        "slot_style",
        "slot_size",
        "ad_upstream",
        "pre_slod_ids",
    ];

    /**
     * 获取可用的上游广告位id列表
     */
    public function getList() {
        if (empty($this->arrUser)) {
            return $this->outJson('', ErrCode::ERR_NOT_LOGIN);
        }
        $strAppId = $this->input->get('app_id');
        if (empty($strAppId)) {
            return $this->outJson('', ErrCode::ERR_INVALID_PARAMS, 'app_id error');
        }
        $pn = empty($this->input->get('currentPage')) ? 1 : intval($this->input->get('currentPage'));
        $rn = empty($this->input->get('pageSize')) ? 10 : intval($this->input->get('pageSize'));
        $this->load->model('PreAdSlotManager');
        $arrData = $this->PreAdSlotManager->getPreSlotIdList($strAppId, $pn, $rn);
        $this->outJson($arrData, ErrCode::OK);
    }

	/**
     * 插入/追加预生成广告位
	 */
	public function add() {
        if (empty($this->arrUser)) {
            return $this->outJson('', ErrCode::ERR_NOT_LOGIN);
        }
        $arrPostParams = json_decode(file_get_contents('php://input'), true);


        if (empty($arrPostParams['app_id'])
            || empty($arrPostParams['slotmap_type'])
            || !is_array($arrPostParams['slotmap_type'])
            || count($arrPostParams['slotmap_type']) !== 3
            || empty($arrPostParams['slotmap_list'])) {
            return $this->outJson('', ErrCode::ERR_INVALID_PARAMS);
        }
        if (preg_match('#[^a-zA-Z0-9,]#', $arrPostParams['slotmap_list'])) {
            return $this->outJson('', ErrCode::ERR_INVALID_PARAMS, '预生成id列表请确保只有数字字母和英文逗号');
        }
        $app_id = $arrPostParams['app_id'];
        $pre_slod_ids = $arrPostParams['slotmap_list'];
        list($slot_style,$ad_upstream,$slot_size) = $arrPostParams['slotmap_type'];

        $this->load->model('PreAdSlotManager');
        // check media_info 的 app_id_map 是否存在此上游
        $arrAppIdMap = $this->PreAdSlotManager->checkMediaLigal($app_id);
        if (!array_key_exists($ad_upstream, $arrAppIdMap)) {
            return $this->outJson('', ErrCode::ERR_SYSTEM, '您添加的预生成id对应的上游并不存在,请检查上游id是否存在');
        }

        $arrParams = compact('app_id', 'slot_style', 'ad_upstream', 'slot_size', 'pre_slod_ids');
        $arrPreAdSlotBefore = $this->PreAdSlotManager->getPreAdSlot($arrParams);

        $arrUpStreamSlotIds = [];
        $arrFormatSlotIds = explode(',', $arrParams['pre_slod_ids']);
        foreach ($arrFormatSlotIds as $key => &$val) {
            if (empty($val)) {
                unset($arrFormatSlotIds[$key]);
                continue;
            }
            $val = trim($val);
        }
        if (empty($arrFormatSlotIds)
            || !is_array($arrFormatSlotIds)) {
            return $this->outJson($arrFormatSlotIds, ErrCode::ERR_INVALID_PARAMS, 'pre_slod_ids 错误');
        }
        foreach ($arrFormatSlotIds as $slotid) {
            $arrPreSlotId[$slotid] = 0;
        }
        $arrUpStreamSlotIds[$arrParams['ad_upstream']][$arrParams['slot_style']][$arrParams['slot_size']] = $arrPreSlotId;
        $arrPreAdSlotAfter = [];
        $arrPreAdSlotAfter = $arrPreAdSlotBefore;
        if (empty($arrPreAdSlotBefore[$arrParams['ad_upstream']])) {
            $arrPreAdSlotAfter[$arrParams['ad_upstream']] = $arrUpStreamSlotIds[$arrParams['ad_upstream']];
        } else {
            if (empty($arrPreAdSlotBefore[$arrParams['ad_upstream']][$arrParams['slot_style']])) {
                $arrPreAdSlotAfter[$arrParams['ad_upstream']][$arrParams['slot_style']] = $arrUpStreamSlotIds[$arrParams['ad_upstream']][$arrParams['slot_style']];
            } else {
                if (empty($arrPreAdSlotBefore[$arrParams['ad_upstream']][$arrParams['slot_style']][$arrParams['slot_size']])) {
                    $arrPreAdSlotAfter[$arrParams['ad_upstream']][$arrParams['slot_style']][$arrParams['slot_size']] = $arrUpStreamSlotIds[$arrParams['ad_upstream']][$arrParams['slot_style']][$arrParams['slot_size']];
                } else {
                    foreach($arrUpStreamSlotIds[$arrParams['ad_upstream']][$arrParams['slot_style']][$arrParams['slot_size']] as $key =>$value){
                        if (array_key_exists($key, $arrPreAdSlotBefore[$arrParams['ad_upstream']][$arrParams['slot_style']][$arrParams['slot_size']])) {
                            unset($arrUpStreamSlotIds[$arrParams['ad_upstream']][$arrParams['slot_style']][$arrParams['slot_size']][$key]);
                        }
                    }
                    $arrPreAdSlotAfter[$arrParams['ad_upstream']][$arrParams['slot_style']][$arrParams['slot_size']]
                        =
                        $arrUpStreamSlotIds[$arrParams['ad_upstream']][$arrParams['slot_style']][$arrParams['slot_size']]
                        +
                        $arrPreAdSlotBefore[$arrParams['ad_upstream']][$arrParams['slot_style']][$arrParams['slot_size']];
                }
            }
        }

        $bolRes = $this->PreAdSlotManager->insertPreAdSlot($arrParams['app_id'], json_encode($arrPreAdSlotAfter));
        if (!$bolRes) {
            return $this->outJson($arrPreAdSlotAfter, ErrCode::ERR_SYSTEM, '预生成广告位更新失败');
        }

        // 直接返回给前端展示
        $arrPreAdSlotIdsDisplay = [];
        $this->config->load('style2platform_map');
        $arrStyleMap = $this->config->item('style2platform_map');
        foreach ($arrPreAdSlotAfter as $strUpstream => $arrStyle) {
            foreach ($arrStyle as $intStyleId => $arrSize) {
                foreach ($arrSize as $intSizeId => $arrSlotIds) {
                    if (empty($arrStyleMap[$intStyleId])) {
                        continue;
                    }
                    $strDisStyle = $arrStyleMap[$intStyleId]['des'];
                    $strDisSize = $arrStyleMap[$intStyleId][$strUpstream]['size'][$intSizeId];
                    $arrPreAdSlotIdsDisplay[$strUpstream][$strDisStyle][$strDisSize] = $arrSlotIds;
                }
            }
        }
        return $this->outJson($arrPreAdSlotIdsDisplay, ErrCode::OK, '预生成广告位更新成功');
    }
}
