<?php
class Media extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->library('DbUtil');
    }

    /**
     *
     */
    public function getAppSecretAndAppIdMap($strAppId) {
        $arrSelect = [
            'select' => 'app_id_map,app_secret',
            'where' => "app_id='" . $strAppId . "'",
        ]; 
        $arrRes = $this->dbutil->getMedia($arrSelect);
        if (!empty($arrRes[0])) {
            return $arrRes[0];
        }
        return [];
    }

    /**
     * @param string $app_id
     * @return array
     */
    public function getMediaInfo($app_id) {
        $arrSelect = [
            'select' => 'app_id,media_name,media_platform,app_detail_url,app_package_name,app_verify_url,app_secret,bg_verify_url,check_status,media_keywords,media_desc,url,app_platform,industry,media_delivery_method',
            'where' => "app_id='" . $app_id . "'",
        ]; 
        $arrRes = $this->dbutil->getMedia($arrSelect);
        if (!empty($arrRes[0])) {
            return $arrRes[0];
        }
        return [];
    }

    /**
     * @param array $arrParams
     * @return bool
     */
    public function insertMediaInfo($arrParams) {
        $strMd5Info = empty($arrParams['app_package_name']) ? md5($arrParams['url']) : md5($arrParams['app_package_name']);
        $arrParams['app_id'] = substr($strMd5Info, 27, 5) . $this->dbutil->getAutoincrementId('media') . rand(0,9);
        $arrRes = $this->dbutil->setMedia($arrParams);
        if ($arrRes['code'] !== 0) {
            if ($arrRes['code'] === 1062) {
                log_message('error', 'Duplicate entry of ' . $arrRes['message']);
                ErrCode::$msg = $arrRes['message'] . '已经被使用';
            }
            return false;
        }
        return true;
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
    public function getPassedMediaList($strAccountId) {
        $this->load->library('DbUtil');
        $arrSelect = [
            'select' => 'app_id,media_name,media_platform,default_valid_style',
            'where' => "account_id='" . $strAccountId . "' AND check_status=3",
        ];
        $arrRes = $this->dbutil->getMedia($arrSelect);
        $arrList = [];
        if (!empty($arrRes)) {
            foreach ($arrRes as $val) {
                $arrList[$val['app_id']]= [
                    'media_name' => $val['media_name'],
                    'default_valid_style' => $val['default_valid_style'],
                    'media_platform' => $val['media_platform'],
                ];
            }
        }
        return [
            'list' => $arrList,
        ];
    } 

    /**
     * @param array
     * @return array 
     */
    public function getMediaList($strAccountId, $pn, $rn, $intCount, $strMediaName, $strStatus) {
        $this->load->library('DbUtil');
        if ($intCount === 0) {
            $arrSelect = [
                'select' => 'count(*) as total',
                'where' => "account_id='" . $strAccountId . "'",
            ];
            $arrRes = $this->dbutil->getMedia($arrSelect);
            if (empty($arrRes)) {
                $arrRes = [];
                $intCount = 0;
                return [
                    'list' => $arrRes,
                    'pagination' => [
                        'total' => $intCount,
                        'pageSize' => $rn,
                        'current' => $pn,
                    ],
                ];
            } 
        }
        $intCount = intval($arrRes[0]['total']);
        $arrSelect = [
            'select' => 'app_id,media_name,app_platform,check_status,media_delivery_method,media_platform,app_verify_url,bg_verify_url,create_time',
            'where' => "account_id='" . $strAccountId . "'",
            'order_by' => 'create_time DESC',
            'limit' => $rn*($pn-1) . ',' . $rn,
        ];
        if (!empty($strMediaName)) {
            $arrSelect['where'] .= " AND media_name like '%" . $strMediaName . "%'"; 
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
     * @param array $arrData
     * @return $array
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
