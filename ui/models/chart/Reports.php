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
        $arrSelect = [
            'select' => '*',
            'where' => " date='" .$arrParams['date']. "'",
            'order_by' => 'create_time DESC',
            'limit' => $rn*($pn-1) . ',' . $rn,
        ];
        $method = $arrParams['method'];
        $arrRes = $this->dbutil->$method($arrSelect);
        if(empty($arrRes[0])) {
            return [
                'list' => [],
                'pagination' => [
                    'total' => $intCount,
                    'pageSize' => $rn,
                    'current' => $pn,
                ],
            ];
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
        $arrSelect = [
            'select' => 'count(*) as total',
            'where' => "date='" .$arrParams['date']. "'",
        ];
        $method = $arrParams['method'];
        $arrRes = $this->dbutil->$method($arrSelect);
        $intCount = $arrRes[0] ? intval($arrRes[0]['total']) : 0;
        return $intCount;
    }//}}}//

}
