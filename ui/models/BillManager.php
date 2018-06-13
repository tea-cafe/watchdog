<?php
class BillManager extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function getAppBillList($strAccountId, $pn, $rn) {
        $timeNow = time();
        $timeStart = mktime(0,0,0,date("m",$timeNow)-1,1,date("Y",$timeNow));
        $timeEnd = mktime(0,0,0,date("m",$timeNow),1,date("Y",$timeNow));

        $sqlCount = 'SELECT COUNT(*) AS intcount From monthly_bill WHERE time>=' . $timeStart . " AND account_id='" . $strAccountId . "'";
        $arrCount = $this->db->query($sqlCount)->result_array();
        if ($this->db->error()['code'] !== 0) {
            ErrCode::$msg = '月账单信息获取失败';
            return [];
        }
        $intCount = intval($arrCount[0]['intcount']);

        $sql = 'SELECT app_id,money From monthly_bill WHERE create_time>=' . $timeStart . ' AND account_id=\'' . $strAccountId . "' limit " . $rn*($pn-1) . ',' . $rn;
        $arrAppMoney = $this->db->query($sql)->result_array();
        if ($this->db->error()['code'] !== 0) {
            ErrCode::$msg = '月账单信息获取失败';
            return [];
        }

        $strSqlSelectMediaName = 'SELECT app_id,media_name,media_platform FROM media_info WHERE account_id=\'' . $strAccountId . "'";
        $arrMediaNames = $this->db->query($strSqlSelectMediaName)->result_array();
        if ($this->db->error()['code'] !== 0) {
            ErrCode::$msg = '月账单信息获取失败';
            return [];
        }
        $arrFormatMediaNames = [];
        foreach($arrMediaNames as $arrMediaName) {
            $arrFormatMediaNames[$arrMediaName['app_id']]['media_name'] = $arrMediaName['media_name'];
            $arrFormatMediaNames[$arrMediaName['app_id']]['media_platform'] = $arrMediaName['media_platform'];
        }

        $arrRes = [];
        foreach ($arrAppMoney as $arrMoney) {
            $arrRes[] = [
                'media_name' => $arrFormatMediaNames[$arrMoney['app_id']]['media_name'],
                'media_platform' => $arrFormatMediaNames[$arrMoney['app_id']]['media_platform'],
                'period' => date('Y-m', $timeStart), 
                'money' => $arrMoney['money'],
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
    }




}
