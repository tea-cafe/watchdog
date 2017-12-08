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
            'select' => 'media_platform,app_id,app_id_map,media_name,proportion,check_status,default_valid_style,industry,app_platform,app_package_name,url,media_keywords,media_desc,app_detail_url,app_verify_url,update_time',
            'where' => "app_id='" . $strAppId . "'",
        ];

        $arrRes = $this->dbutil->getMedia($arrSelect);
        if (empty($arrRes[0])) {
            return [];
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
    public function getMediaLists($pn, $rn, $intCount, $strMediaName, $strStatus) {
        $this->load->library('DbUtil');
        if ($intCount === 0) {
            $arrSelect = [
                'select' => 'count(*) as total',
            ];
            $arrRes = $this->dbutil->getMedia($arrSelect);
            $intCount = intval($arrRes[0]['total']);
        }
        $arrSelect = [
            'select' => 'app_id,industry,media_name,check_status,media_platform,create_time',
            'order_by' => 'create_time DESC',
            'limit' => $rn*($pn-1) . ',' . $rn,
        ];
        if (!empty($strMediaName)) {
            $arrSelect['where'] .= "media_name like '%" . $strMediaName . "%'"; 
        }
        if (!empty($strStatus)) {
            $arrStatus = explode(',', $strStatus);
             $arrSelect['where'] .= " AND (";
            foreach ($arrStatus as $state) {
                $arrSelect['where'] .= "check_status=" . $state . " OR "; 
            }
            $arrSelect['where'] = substr($arrSelect['where'], 0, -4);
            $arrSelect['where'] .= ")";
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

}
