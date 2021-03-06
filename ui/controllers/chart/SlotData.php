<?php
class SlotData extends BG_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model('chart/SlotDataModel');
    }

    /**
     * 获取广告位按日期汇总数据列表
     */
    public function getSlotSumDataList() {//{{{//
        $arrParams = $this->input->get(NULL, TRUE);
        if(empty($arrParams['startDate'])
            || empty($arrParams['endDate'])) {
            return $this->outJson([], ErrCode::ERR_INVALID_PARAMS);
        }
        $arrParams['pn'] = empty($arrParams['pn']) ? 1 : $arrParams['pn'];
        $arrParams['rn'] = empty($arrParams['rn']) ? 10 : $arrParams['rn'];
        $arrParams['method'] = 'getUsrSlotSum';

        $arrList = $this->SlotDataModel->getSlotSumDataList($arrParams);
        return $arrList?$this->outJson($arrList, ErrCode::OK) : $this->outJson([], ErrCode::OK);
    }//}}}//

    /**
     * 获取广告位数据daily
     */
    public function getSlotDailyDataList() {//{{{//
        $arrParams = $this->input->get(NULL, TRUE);
        if(empty($arrParams['startDate'])
            || empty($arrParams['endDate'])
            || empty($arrParams['user_slot_id'])) {
            return $this->outJson([], ErrCode::ERR_INVALID_PARAMS);
        }
        $arrParams['pn'] = empty($arrParams['pn']) ? 1 : $arrParams['pn'];
        $arrParams['rn'] = empty($arrParams['rn']) ? 10 : $arrParams['rn'];
        $arrParams['method'] = 'getUsrSlotSum';

        $arrList = $this->SlotDataModel->getSlotDailyDataList($arrParams);
        return $arrList?$this->outJson($arrList, ErrCode::OK) : $this->outJson([], ErrCode::OK);
    }//}}}//

}
