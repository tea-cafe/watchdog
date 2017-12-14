<?php
class MediaManager extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->library('DbUtil');
    }

    /**
     * @param array $arrParams
     * @return bool
     */
    public function updateMediaInfo($arrParams) {
        $arrRes = $this->dbutil->udpMedia($arrParams);
        if ($arrRes['code'] !== 0) {
            return false;
        }
        return true;
    }

    /**
     * @param array $arrParams
     * @return bool
     */
    public function getMediaDetail($strAppId) {
        $arrSelect = [
            'select' => 'media_platform,app_id,app_id_map,media_name,proportion,check_status,default_valid_style,industry,app_platform,app_package_name,app_secret,url,media_keywords,media_desc,app_detail_url,app_verify_url,update_time,create_time',
            'where' => "app_id='" . $strAppId . "'",
        ];

        $arrRes = $this->dbutil->getMedia($arrSelect);
        if (empty($arrRes[0])) {
            return [];
        }

        if (strpos($arrRes[0]['default_valid_style'], 7) !== false) {
            $arrAppIdMap = json_decode($arrRes[0]['app_id_map'], true);
            foreach ($arrAppIdMap as $appid => &$val) {
                if ($appid === 'TUIA') {
                    $val .= '|' . $arrRes[0]['app_secret'];
                    break;
                }
            }
        }
        $arrRes = $this->industryMap($arrRes);
        return $arrRes[0];
    }

    /**
     * @param string $strAppId
     * @return array;
     */
    public function getMediaValidSlotIds($strAppId) {
        $arrSelect = [
            'select' => 'valid_slot_ids',
            'where' => "appid='" . $strAppId . "'",
        ];
        $arrRes = $this->dbutil->getMedia($arrSelect);
        if (!empty($arrRes[0]['valid_slot_ids'])) {
            return explode(',', $arrRes[0]['valid_slot_ids']);
        }
        return [];
    }

    /**
     * @param array
     * @return array 
     */
    public function getMediaList($pn, $rn, $account_id, $strStatus, $strMediaName) {
        $this->load->library('DbUtil');
        $arrSelect = [
            'select' => 'count(*) as total',
        ];
        $arrRes = $this->dbutil->getMedia($arrSelect);
        $intCount = empty($arrRes[0]['total']) ? 10 : intval($arrRes[0]['total']);
        $arrSelect = [
            'select' => 'app_id,industry,media_name,check_status,media_platform,media_delivery_method,create_time',
            'order_by' => 'create_time DESC',
            'limit' => $rn*($pn-1) . ',' . $rn,
        ];

        if (!empty($account_id)) {
            $arrSelect['where'] = "account_id='" . $account_id . "'"; 
        }
        if (!empty($strStatus)) {
            $arrStatus = explode(',', $strStatus);
            if (empty($arrSelect['where'])) {
                $arrSelect['where'] = "(";
            } else {
                $arrSelect['where'] .= " AND (";
            }
            foreach ($arrStatus as $state) {
                $arrSelect['where'] .= "check_status=" . $state . " OR "; 
            }
            $arrSelect['where'] = substr($arrSelect['where'], 0, -4);
            $arrSelect['where'] .= ")";
        }

        if (!empty($strMediaName)) {
            if (empty($arrSelect['where'])) {
                $arrSelect['where'] = "media_name like '%" . $strMediaName . "%'";
            } else {
                $arrSelect['where'] .= " AND media_name like '%" . $strMediaName . "%'";
            }

        }
        $arrRes = $this->dbutil->getMedia($arrSelect);
        $arrRes = $this->industryMap($arrRes);
        return [
            'list' => $arrRes,
            'pagination' => [
                'total' => $intCount,
                'pageSize' => $rn,
                'current' => $pn,
            ],
        ];
    } 

    /**
     * industry id 2 文字
     */
    private function industryMap($arrData) {
        $this->config->load('industry');
        $arrIndustryMap = $this->config->item('industry');
        foreach ($arrData as &$val) {
            $idTmp = explode('-', $val['industry']);
            $val['industry'] = empty($arrIndustryMap[$idTmp[0]][$idTmp[1]]) ? '' : $arrIndustryMap[$idTmp[0]][$idTmp[1]]['sub'] . '-' . $arrIndustryMap[$idTmp[0]][$idTmp[1]]['text']; 
        }
        return $arrData;
     }

    /**
     * getMediaByAppId
     */
    public function getMediaByAppId($strAppId) {//{{{//
        $arrSelect = [
            'select' => 'media_platform,media_name',
            'where' => "app_id='" . $strAppId . "'",
        ];

        $arrRes = $this->dbutil->getMedia($arrSelect);
        if (empty($arrRes[0])) {
            return false;
        }
        return $arrRes;
    }//}}}//

}
