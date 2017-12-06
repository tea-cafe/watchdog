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
     *
	 */
	public function index() {
        $arrPostParams = json_decode(file_get_contents('php://input'), true);
        if (empty($this->arrUser)) {
            return $this->outJson('', ErrCode::ERR_NOT_LOGIN);
        }
        foreach ($arrPostParams as $key => &$val) {
            if(!in_array($key, self::VALID_PRESLOT_KEY)) {
                return $this->outJson('', ErrCode::ERR_INVALID_PARAMS); 
            }
            $val = $this->security->xss_clean($val);
        }
        
        $this->load->model('PreAdSlotManager');
        $arrPreAdSlotBefore = $this->PreAdSlotManager->getPreAdSlot($arrPostParams);

        $arrUpStreamSlotIds = [];
        $arrFormatSlotIds = explode(',', $arrPostParams['pre_slod_ids']);
        foreach ($arrFormatSlotIds as $slotid) {
            $arrPreSlitId[$slotid] = 0; 
        }
        $arrUpStreamSlotIds[$arrPostParams['ad_upstream']][$arrPostParams['slot_style']][$arrPostParams['slot_size']] = $arrPreSlitId; 

        $arrPreAdSlotAfter = array_merge($arrUpStreamSlotIds, $arrPreAdSlotBefore);

        $bolRes = $this->PreAdSlotManager->insertPreAdSlot($arrPostParams['app_id'], json_encode($arrPreAdSlotAfter));
        if (!$bolRes) {
            return $this->outJson($arrPreAdSlotAfter, ErrCode::ERR_SYSTEM, '预生成广告位更新失败');
        }

        $arrPreAdSlotIdsDisplay = [];
        $this->config->load('style2platform_map');
        $arrStyleMap = $this->config->item('style2platform_map');
        foreach ($arrPreAdSlotAfter as $strUpstream => $arrStyle) {
            foreach ($arrStyle as $intStyleId => $arrSize) {
                foreach ($arrSize as $intSizeId => $arrSlotIds) {
                    $strDisStyle = $arrStyleMap[$intStyleId]['des'];
                    $strDisSize = $arrStyleMap[$intStyleId][$strUpstream]['size'][$intSizeId];
                    $arrPreAdSlotIdsDisplay[$strUpstream][$strDisStyle][$strDisSize] = $arrSlotIds;
                }
            }
        }
        return $this->outJson($arrPreAdSlotIdsDisplay, ErrCode::OK, '预生成广告位更新成功');
    }
}
