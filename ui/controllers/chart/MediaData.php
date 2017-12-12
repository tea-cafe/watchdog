<?php
class MediaData extends BG_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model('chart/MediaDataModel');
    }

    /**
     * 获取媒体按日期汇总数据列表
     */
    public function getMediaSumDataList() {//{{{//
        $arrParams = $this->input->get(NULL, TRUE);
        if(empty($arrParams['startDate'])
            || empty($arrParams['endDate'])) {
            return $this->outJson([], ErrCode::ERR_INVALID_PARAMS);
        }
        $arrParams['pn'] = empty($arrParams['pn']) ? 1 : $arrParams['pn'];
        $arrParams['rn'] = empty($arrParams['rn']) ? 10 : $arrParams['rn'];

        $arrList = $this->MediaDataModel->getMediaSumDataList($arrParams);
        return $arrList?$this->outJson($arrList, ErrCode::OK) : $this->outJson([], ErrCode::OK);
    }//}}}//

    /**
     * 获取媒体数据daily
     */
    public function getMediaDailyDataList() {//{{{//
        $arrParams = $this->input->get(NULL, TRUE);
        if(empty($arrParams['startDate'])
            || empty($arrParams['endDate'])
            || empty($arrParams['app_id'])
            || empty($arrParams['platform'])) {
            return $this->outJson([], ErrCode::ERR_INVALID_PARAMS);
        }
        $arrParams['pn'] = empty($arrParams['pn']) ? 1 : $arrParams['pn'];
        $arrParams['rn'] = empty($arrParams['rn']) ? 10 : $arrParams['rn'];

        $arrList = $this->MediaDataModel->getMediaDailyDataList($arrParams);
        return $arrList?$this->outJson($arrList, ErrCode::OK) : $this->outJson([], ErrCode::OK);
    }//}}}//

}
