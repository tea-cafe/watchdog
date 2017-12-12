<?php
class Reports extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->library('DbUtil');
    }

    public function getViewList($arrParams) {//{{{//
        $intCount = 0;
        if(!isset($arrParams['count']) || $arrParams['count'] == 0) {
            $intCount = $this->getTotalCount($arrParams);
        }
        $rn = $arrParams['rn'];
        $pn = $arrParams['pn'];
        $mark = intval($arrParams['mark']);
        $arrSelect = [
            'select' => '*',
            'where' => "mark=".$mark." AND date='" .$arrParams['date']. "'",
            'order_by' => 'create_time DESC',
            'limit' => $rn*($pn-1) . ',' . $rn,
        ];
        $method = $arrParams['method'];
        $arrRes = $this->dbutil->$method($arrSelect);
        if(empty($arrRes[0])) {
            return false;
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

    private function getTotalCount($arrParams) {//{{{//
        $mark = intval($arrParams['mark']);
        $arrSelect = [
            'select' => 'count(*) as total',
            'where' => "mark=".$mark." AND date='" .$arrParams['date']. "'",
        ];
        $method = 'getOriProfit'.$arrParams['source'];
        $arrRes = $this->dbutil->$method($arrSelect);
        $intCount = $arrRes[0] ? $arrRes[0]['total'] : 0;
        return $intCount;
    }//}}}//

}
