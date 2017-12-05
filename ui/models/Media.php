<?php
class Media extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->library('DbUtil');
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
    public function getMediaLists($intAccountId, $pn, $rn, $intCount, $strMediaName, $strStatus) {
        $this->load->library('DbUtil');
        if ($intCount === 0) {
            $arrSelect = [
                'select' => 'count(*) as total',
                'where' => 'account_id=' . $intAccountId,
            ];
            $arrRes = $this->dbutil->getMedia($arrSelect);
            $intCount = intval($arrRes[0]['total']);
        }
        $arrSelect = [
            'select' => 'app_id,media_name,check_status,media_platform,create_time',
            'where' => 'account_id=' . $intAccountId,
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

}
