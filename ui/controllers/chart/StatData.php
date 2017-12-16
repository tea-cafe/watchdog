<?php
class StatData extends BG_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model('chart/StatDataModel');
    }

    /**
     * 获取按日期汇总数据列表
     * type 取值Acct, Media, Slot
     */
    public function getStatSumDataList() {//{{{//
        $arrParams = $this->input->get(NULL, TRUE);
        if(empty($arrParams['startDate'])
            || empty($arrParams['endDate'])
            || empty($arrParams['type'])
            || empty($arrParams['statId'])
            || !in_array($arrParams['type'], ['Acct', 'Media', 'Slot'])) {
            return $this->outJson([], ErrCode::ERR_INVALID_PARAMS);
        }
        $arrParams['pn'] = empty($arrParams['pn']) ? 1 : $arrParams['pn'];
        $arrParams['rn'] = empty($arrParams['rn']) ? 10 : $arrParams['rn'];
        $arrParams['method'] = 'getUsr'.$arrParams['type'].'Sum';
        $arrParams['lastday'] = $arrParams['endDate'];//date("Y-m-d",strtotime("-1 day"));

        $arrList = $this->StatDataModel->getSumDataList($arrParams);
        return $arrList?$this->outJson($arrList, ErrCode::OK) : $this->outJson([], ErrCode::OK);
    }//}}}//

    /**
     * 获取daily数据列表
     */
    public function getStatDailyDataList() {//{{{//
        $arrParams = $this->input->get(NULL, TRUE);
        if(empty($arrParams['startDate'])
            || empty($arrParams['endDate'])
            || empty($arrParams['type'])
            || empty($arrParams['statId'])
            || !in_array($arrParams['type'], ['Acct', 'Media', 'Slot'])) {
            return $this->outJson([], ErrCode::ERR_INVALID_PARAMS);
        }
        $arrParams['pn'] = empty($arrParams['pn']) ? 1 : $arrParams['pn'];
        $arrParams['rn'] = empty($arrParams['rn']) ? 10 : $arrParams['rn'];

        $arrParams['method'] = 'getUsr'.$arrParams['type'].'Sum';
        $arrList = $this->StatDataModel->getDailyDataList($arrParams);
        return $arrList?$this->outJson($arrList, ErrCode::OK) : $this->outJson([], ErrCode::OK);
    }//}}}//

}
