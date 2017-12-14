<?php
class AcctData extends BG_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model('chart/AcctDataModel');
    }

    /**
     * 获取媒体按日期汇总数据列表
     */
    public function getAcctSumDataList() {//{{{//
        $arrParams = $this->input->get(NULL, TRUE);
        if(empty($arrParams['startDate'])
            || empty($arrParams['endDate'])) {
            return $this->outJson([], ErrCode::ERR_INVALID_PARAMS);
        }
        $arrParams['pn'] = empty($arrParams['pn']) ? 1 : $arrParams['pn'];
        $arrParams['rn'] = empty($arrParams['rn']) ? 10 : $arrParams['rn'];
        $arrParams['method'] = 'getUsrAcctSum';

        $arrList = $this->AcctDataModel->getAcctSumDataList($arrParams);
        return $arrList?$this->outJson($arrList, ErrCode::OK) : $this->outJson([], ErrCode::OK);
    }//}}}//

    /**
     * 获取媒体数据daily
     */
    public function getAcctDailyDataList() {//{{{//
        $arrParams = $this->input->get(NULL, TRUE);
        if(empty($arrParams['startDate'])
            || empty($arrParams['endDate'])
            || empty($arrParams['account_id'])) {
            return $this->outJson([], ErrCode::ERR_INVALID_PARAMS);
        }
        $arrParams['pn'] = empty($arrParams['pn']) ? 1 : $arrParams['pn'];
        $arrParams['rn'] = empty($arrParams['rn']) ? 10 : $arrParams['rn'];

        $arrParams['method'] = 'getUsrAcctSum';
        $arrList = $this->AcctDataModel->getAcctDailyDataList($arrParams);
        return $arrList?$this->outJson($arrList, ErrCode::OK) : $this->outJson([], ErrCode::OK);
    }//}}}//

}
