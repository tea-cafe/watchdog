<?php
class Report extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model('chart/Reports');
    }

    /**
     * 预览展示
     */
    public function getPreviewList() {//{{{//
        $arrParams = $this->input->get(NULL, TRUE);
        if(empty($arrParams['date'])
            || empty($arrParams['source'])) {
            return $this->outJson([], ErrCode::ERR_INVALID_PARAMS);
        }
        $arrParams['pn'] = empty($arrParams['pn']) ? 1 : $arrParams['pn'];
        $arrParams['rn'] = empty($arrParams['rn']) ? 10 : $arrParams['rn'];
        $arrParams['mark'] = 0;
        $arrParams['method'] = 'getOriProfit'.$arrParams['source'];
        $arrList = $this->Reports->getViewList($arrParams);
        return $arrList?$this->outJson($arrList, ErrCode::OK) : $this->outJson([], ErrCode::OK);
    }//}}}//

    /**
     * 载入后展示
     */
    public function getLoadedList() {//{{{//
        $arrParams = $this->input->get(NULL, TRUE);
        if(empty($arrParams['date'])
            || empty($arrParams['source'])) {
            return $this->outJson([], ErrCode::ERR_INVALID_PARAMS);
        }
        $arrParams['pn'] = empty($arrParams['pn']) ? 1 : $arrParams['pn'];
        $arrParams['rn'] = empty($arrParams['rn']) ? 10 : $arrParams['rn'];
        $arrParams['mark'] = 1;
        $arrParams['method'] = 'getOriProfit'.$arrParams['source'];
        $arrList = $this->Reports->getViewList($arrParams);
        return $arrList?$this->outJson($arrList, ErrCode::OK) : $this->outJson([], ErrCode::OK);
    }//}}}//

    /**
     * 广告位汇总
     */
    public function getSlotSumList() {//{{{//
        $arrParams = $this->input->get(NULL, TRUE);
        if(empty($arrParams['date'])
            || empty($arrParams['source'])) {
            return $this->outJson([], ErrCode::ERR_INVALID_PARAMS);
        }
        $arrParams['pn'] = empty($arrParams['pn']) ? 1 : $arrParams['pn'];
        $arrParams['rn'] = empty($arrParams['rn']) ? 10 : $arrParams['rn'];
        $arrParams['mark'] = 1;
        $arrParams['method'] = 'getUsrSlotSum';
        $arrList = $this->Reports->getViewList($arrParams);
        return $arrList?$this->outJson($arrList, ErrCode::OK) : $this->outJson([], ErrCode::OK);
    }//}}}//

    /**
     * 媒体汇总
     */
    public function getMediaSumList() {//{{{//
        $arrParams = $this->input->get(NULL, TRUE);
        if(empty($arrParams['date'])
            || empty($arrParams['source'])) {
            return $this->outJson([], ErrCode::ERR_INVALID_PARAMS);
        }
        $arrParams['pn'] = empty($arrParams['pn']) ? 1 : $arrParams['pn'];
        $arrParams['rn'] = empty($arrParams['rn']) ? 10 : $arrParams['rn'];
        $arrParams['mark'] = 1;
        $arrParams['method'] = 'getUsrMediaSum';
        $arrList = $this->Reports->getViewList($arrParams);
        return $arrList?$this->outJson($arrList, ErrCode::OK) : $this->outJson([], ErrCode::OK);
    }//}}}//

    /**
     * 渠道汇总
     */
    public function getAcctSumList() {//{{{//
        $arrParams = $this->input->get(NULL, TRUE);
        if(empty($arrParams['date'])
            || empty($arrParams['source'])) {
            return $this->outJson([], ErrCode::ERR_INVALID_PARAMS);
        }
        $arrParams['pn'] = empty($arrParams['pn']) ? 1 : $arrParams['pn'];
        $arrParams['rn'] = empty($arrParams['rn']) ? 10 : $arrParams['rn'];
        $arrParams['mark'] = 1;
        $arrParams['method'] = 'getUsrAcctSum';
        $arrList = $this->Reports->getViewList($arrParams);
        return $arrList?$this->outJson($arrList, ErrCode::OK) : $this->outJson([], ErrCode::OK);
    }//}}}//
}
