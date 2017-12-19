<?php
class MediaDataModel extends CI_Model {
    public function __construct() {
        parent::__construct();
        $this->load->library('DbUtil');
    }

    public function getMediaSumDataList($arrParams) {//{{{//
        $intCount = 0;
        if(!isset($arrParams['count']) || $arrParams['count'] == 0) {
            $intCount = $this->getTotalCount($arrParams);
        }

        $rn = $arrParams['rn'];
        $pn = $arrParams['pn'];
        $arrSelect = [
            'select' => '*',
            'where' => "date>='" .$arrParams['startDate']. "' AND date<='".$arrParams['endDate']."'",
            'order_by' => 'date ASC',
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
                'curve' => [],
            ];
        }

        $arrRet = $this->formatMediaData($arrRes);
        $arrDate = $this->formatMediaDataByDate($arrRes);
        $arrCurve = $this->formatCurve($arrDate);

        return [
            'list' => $arrRet,
            'pagination' => [
                'total' => $intCount,
                'pageSize' => $rn,
                'current' => $pn,
            ],
            'curve' => $arrCurve,
        ];
    }//}}}//

    public function getMediaDailyDataList($arrParams) {//{{{//
        $intCount = 0;
        if(!isset($arrParams['count']) || $arrParams['count'] == 0) {
            $intCount = $this->getTotalCount($arrParams);
        }

        $rn = $arrParams['rn'];
        $pn = $arrParams['pn'];
        $arrSelect = [
            'select' => '*',
            'where' => "date>='" .$arrParams['startDate']. "' AND date<='".$arrParams['endDate']."' AND app_id= '".$arrParams['app_id']."' AND platform='".$arrParams['platform']."'",
            'order_by' => 'date DESC',
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
            'where' => "date>'" .$arrParams['startDate']. "' AND date< '".$arrParams['endDate']."'",
        ];
        $method = $arrParams['method'];
        $arrRes = $this->dbutil->$method($arrSelect);
        $intCount = $arrRes[0] ? intval($arrRes[0]['total']) : 0;
        return $intCount;
    }//}}}//

    private function formatMediaData($arrRes) {//{{{//
        $arrOriData = [];
        foreach($arrRes as $key=>$val) {
            $arrOriData[$val['app_id']]['app_id'] = $val['app_id'];
            $arrOriData[$val['app_id']]['account_id'] = $val['account_id'];
            $arrOriData[$val['app_id']]['media_name'] = $val['media_name'];
            $arrOriData[$val['app_id']]['platform'] = $val['platform'];
            $arrOriData[$val['app_id']]['pre_exposure_num'] = empty($arrOriData[$val['app_id']]['pre_exposure_num'])
                ? intval($val['pre_exposure_num']) : intval($val['pre_exposure_num']) + $arrOriData[$val['app_id']]['pre_exposure_num'];
            $arrOriData[$val['app_id']]['post_exposure_num'] = empty($arrOriData[$val['app_id']]['post_exposure_num'])
                ? intval($val['post_exposure_num']) : intval($val['post_exposure_num']) + $arrOriData[$val['app_id']]['post_exposure_num'];
            $arrOriData[$val['app_id']]['pre_click_num'] = empty($arrOriData[$val['app_id']]['pre_click_num'])
                ? intval($val['pre_click_num']) : intval($val['pre_click_num']) + $arrOriData[$val['app_id']]['pre_click_num'];
            $arrOriData[$val['app_id']]['post_click_num'] = empty($arrOriData[$val['app_id']]['post_click_num'])
                ? intval($val['post_click_num']) : intval($val['post_click_num']) + $arrOriData[$val['app_id']]['post_click_num'];
            $arrOriData[$val['app_id']]['pre_profit'] = empty($arrOriData[$val['app_id']]['pre_profit'])
                ? intval($val['pre_profit']) : intval($val['pre_profit']) + $arrOriData[$val['app_id']]['pre_profit'];
            $arrOriData[$val['app_id']]['post_profit'] = empty($arrOriData[$val['app_id']]['post_profit'])
                ? floatval($val['post_profit']) : floatval($val['post_profit']) + $arrOriData[$val['app_id']]['post_profit'];
            $arrOriData[$val['app_id']]['click_rate'] = 0;
            $arrOriData[$val['app_id']]['cpc'] = 0;
            $arrOriData[$val['app_id']]['ecpm'] = 0;
            $arrOriData[$val['app_id']]['mark'] = 1;
            $arrOriData[$val['app_id']]['date'] = $val['date'];
            $arrOriData[$val['app_id']]['create_time'] = time();
            $arrOriData[$val['app_id']]['update_time'] = time();

        }
        return array_values($arrOriData);
    }//}}}//
    
    private function formatMediaDataByDate($arrRes) {//{{{//
        $arrOriData = [];
        foreach($arrRes as $key=>$val) {
            $arrOriData[$val['date']]['pre_exposure_num'] = empty($arrOriData[$val['date']]['pre_exposure_num'])
                ? intval($val['pre_exposure_num']) : intval($val['pre_exposure_num']) + $arrOriData[$val['date']]['pre_exposure_num'];
            $arrOriData[$val['date']]['post_exposure_num'] = empty($arrOriData[$val['date']]['post_exposure_num'])
                ? intval($val['post_exposure_num']) : intval($val['post_exposure_num']) + $arrOriData[$val['date']]['post_exposure_num'];
            $arrOriData[$val['date']]['pre_click_num'] = empty($arrOriData[$val['date']]['pre_click_num'])
                ? intval($val['pre_click_num']) : intval($val['pre_click_num']) + $arrOriData[$val['date']]['pre_click_num'];
            $arrOriData[$val['date']]['post_click_num'] = empty($arrOriData[$val['date']]['post_click_num'])
                ? intval($val['post_click_num']) : intval($val['post_click_num']) + $arrOriData[$val['date']]['post_click_num'];
            $arrOriData[$val['date']]['pre_profit'] = empty($arrOriData[$val['date']]['pre_profit'])
                ? intval($val['pre_profit']) : intval($val['pre_profit']) + $arrOriData[$val['date']]['pre_profit'];
            $arrOriData[$val['date']]['post_profit'] = empty($arrOriData[$val['date']]['post_profit'])
                ? floatval($val['post_profit']) : floatval($val['post_profit']) + $arrOriData[$val['date']]['post_profit'];
            $arrOriData[$val['date']]['click_rate'] = 0;
            $arrOriData[$val['date']]['cpc'] = 0;
            $arrOriData[$val['date']]['ecpm'] = 0;
            $arrOriData[$val['date']]['mark'] = 1;
            $arrOriData[$val['date']]['date'] = $val['date'];

        }
        return array_values($arrOriData);
    }//}}}//

    private function formatCurve($arrRes) {//{{{//
        $arrRet['exposureCount'] = [];
        $arrRet['clickCount'] = [];
        $arrRet['curDate'] = [];
        $arrRet['clickRate'] = [];
        $arrRet['eCpm'] = [];
        $arrRet['profit'] = [];
        foreach($arrRes as $key => $val) {
            array_push($arrRet['exposureCount'], $val['pre_exposure_num']);  
            array_push($arrRet['clickCount'], $val['pre_click_num']);  
            array_push($arrRet['clickRate'], $val['click_rate']);  
            array_push($arrRet['eCpm'], $val['ecpm']);  
            array_push($arrRet['profit'], $val['pre_profit']);  
            array_push($arrRet['curDate'], $val['date']);  
        }

         $arrRet['exposureCount'] = array_values($arrRet['exposureCount']);
         $arrRet['clickCount'] = array_values($arrRet['clickCount']);
         $arrRet['clickRate'] = array_values($arrRet['clickRate']);
         $arrRet['eCpm'] = array_values($arrRet['eCpm']);
         $arrRet['profit'] = array_values($arrRet['profit']);
         $arrRet['curDate'] = array_values($arrRet['curDate']);
        return $arrRet;
    }//}}}//
}
