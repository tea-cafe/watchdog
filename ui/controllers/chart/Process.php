<?php
class Process extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model('chart/Processes');
    }

    /**
     * 导入
     */
    public function confirmLoad() {//{{{//
        $arrParams = $this->input->get(NULL, TRUE);
        if(empty($arrParams['date'])
            || empty($arrParams['source'])) {
            return $this->outJson([], ErrCode::ERR_INVALID_PARAMS);
        }
        $boolRet = $this->Processes->doConfirmLoad($arrParams);
        return $boolRet?$this->outJson($boolRet, ErrCode::OK) : $this->outJson([], ErrCode::ERR_SYSTEM);
    }//}}}//

    /**
     * 取消导入
     */
    public function cancelLoad() {//{{{//
        $arrParams = $this->input->get(NULL, TRUE);
        if(empty($arrParams['date'])
            || empty($arrParams['source'])) {
            return $this->outJson([], ErrCode::ERR_INVALID_PARAMS);
        }
        $arrParams['method'] = 'delOriProfit'.$arrParams['source'];
        $boolRet = $this->Processes->doCancelLoad($arrParams);
        return $boolRet?$this->outJson($boolRet, ErrCode::OK) : $this->outJson([], ErrCode::ERR_SYSTEM);
    }//}}}//

    /**
     * 汇总
     */
    public function summary() {//{{{//
        $arrParams = $this->input->get(NULL, TRUE);
        if(empty($arrParams['date'])
            || empty($arrParams['source'])) {
            return $this->outJson([], ErrCode::ERR_INVALID_PARAMS);
        }
        $arrParams['method'] = 'delOriProfit'.$arrParams['source'];
        $arrList = $this->Processes->doSummary($arrParams);
        return $arrList?$this->outJson($arrList, ErrCode::OK) : $this->outJson([], ErrCode::OK);
    }//}}}//

    /**
     * 撤销汇总
     */
    public function cancelSummary() {//{{{//
        $arrParams = $this->input->get(NULL, TRUE);
        if(empty($arrParams['date'])
            || empty($arrParams['source'])) {
            return $this->outJson([], ErrCode::ERR_INVALID_PARAMS);
        }
        $arrParams['method'] = 'delOriProfit'.$arrParams['source'];
        $arrList = $this->Processes->doSummary($arrParams);
        return $arrList?$this->outJson($arrList, ErrCode::OK) : $this->outJson([], ErrCode::OK);
    }//}}}//

    /**
     * 获取按钮状态
     */
    public function getBtnState() {//{{{//
        $arrParams = $this->input->get(NULL, TRUE);
        if(empty($arrParams['date'])
            || empty($arrParams['source'])) {
            return $this->outJson([], ErrCode::ERR_INVALID_PARAMS);
        }

        $arrParams['account_id'] = 1;//$this->arrUser['account_id'];//TODO user info
        $arrStateRet = $this->Processes->getBtnState($arrParams);

        return $arrStateRet?$this->outJson($arrStateRet, ErrCode::OK) : $this->outJson([], ErrCode::OK);
    }//}}}//
}
