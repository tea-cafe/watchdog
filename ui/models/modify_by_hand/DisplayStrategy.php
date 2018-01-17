<?php
/**
 * 修改 display_strategy
 * 线下执行：/usr/local/src/php/bin/php /home/work/watchdog/web/index.php ModifyByHand execute "display_strategy"
 * 线上执行：/usr/local/php/bin/php /home/work/watchdog/web/index.php ModifyByHand execute "display_strategy"
 * display_strategy 记录在adslot_style_info
 * 第一步：确定要修改的display_strategy 对应的slot_style
 * 第二步：修改slot_style记录
 * 第三步：根据slot_style查询adslot_info，获取相应的slot_id
 * 第四部：修改data_for_sdk所有的相关记录 
 */
class DisplayStrategy extends CI_Model {

    public function __construct() {
        parent::__construct();
    }
        
    public function do_execute() {
        /*******这里放要修改的数据,添加完后记得删掉修改*********
        $display_strategy = [ // 要修改成的数据
            'BAIDU' => 20,
            'GDT' => 80,
        ];
        $slot_style = '1,2,3'; // 受影响的slot_style，最后不要加","
        **********************************/
        
        if (empty($display_strategy)
            || empty($slot_style)) {
            echo '请在models/modify_by_hand/DisplayStrategy.php中增加要修改的display_strategy和受影响的slot_style';
            exit;
        }

        $arrData['display_strategy'] = json_encode($display_strategy);
        $arrData['where'] = $slot_style;

        $this->load->database();

        $sqlModifyAdslotStyleInfo = "update adslot_style_info set display_strategy='" . $arrData['display_strategy'] . "' where slot_style in (" . $arrData['where'] .')';

        $objRes = $this->db->query($sqlModifyAdslotStyleInfo);
        $arrErr = $this->db->error();
        if ($arrErr['code'] !== 0) {
            echo $arrErr['message'];
            exit;
        } else {
            echo "adslot_style_info 更新成功，一共更改了" . $this->db->affected_rows() . "行\n";
        }

        $sqlSelectSlotId = "select app_id,slot_id from adslot_info where slot_style in (" . $arrData['where'] . ')';
        $objRes = $this->db->query($sqlSelectSlotId);
        $arrErr = $this->db->error();
        if ($arrErr['code'] !== 0) {
            echo $arrErr['message'];
            exit;
        }
        $arrRes = $objRes->result_array();
        if (empty($arrRes)) {
            echo '数据为空，请检查传入的slot_style是否正确';
            exit;
        }
        $arrFormatData = [];
        foreach ($arrRes as $val) {
            $arrFormatData[$val['app_id']][] = $val['slot_id'];
        }
        
        $where = '';
        foreach (array_keys($arrFormatData) as $app_id) {
            $where .= "'" . $app_id . "',";
        }
        $where = substr($where, 0, -1);
        $sqlSelectDataForSdk = "select app_id,data from data_for_sdk where app_id in (" . $where . ')';
        $objRes = $this->db->query($sqlSelectDataForSdk);
        $arrErr = $this->db->error();
        if ($arrErr['code'] !== 0) {
            echo $arrErr['message'];
            exit;
        }
        $arrRes = $objRes->result_array();
        if (empty($arrRes)) {
            echo '查询data_for_sdk数据为空，请检查传入的上一步是否正确';
            exit;
        }
        $sqlUpdateDataForSdk = 'insert into data_for_sdk(app_id,data) values';
        $strValues = '';
        $arrRecode = [];
        foreach ($arrRes as $val) {
            $arrData = json_decode($val['data'], true);
            foreach ($arrData as $slot_id => &$arrInfo) {
                if (in_array($slot_id, $arrFormatData[$val['app_id']])) {
                    $arrInfo['strategy'] = $display_strategy; 
                    $arrRecode[$val['app_id']][] = $slot_id;
                } 
                $strValues .= "('" . $val['app_id'] . "','" . json_encode($arrData) . "'),";
            }
        }
        $strValues = substr($strValues, 0, -1);
        $sqlUpdateDataForSdk .= $strValues . " on duplicate key update data=values(data)";
        $objRes = $this->db->query($sqlUpdateDataForSdk);
        $arrErr = $this->db->error();
        if ($arrErr['code'] !== 0) {
            echo $arrErr['message'];
            exit;
        }
        echo "修改data_for_sdk成功，受影响行数：" . $this->db->affected_rows() . "\n";
        echo "受影响范围为:\n";
        var_dump($arrRecode);
    }
}

