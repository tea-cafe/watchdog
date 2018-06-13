<?php
/*GDT的ID过长，抑制Notice错误*/
error_reporting(E_ALL & ~E_NOTICE);

/**
 * 渠道信息列表
 */
class DataWarningManager extends CI_Model{
	const SLOT_DB_NAME = [
		'BAIDU' => 'getOriprofitBaidu',
		'GDT' => 'getOriprofitGdt',
		'TUIA' => 'getOriprofitTuia',
		'YEZI' => 'getOriprofitYezi',
	];
	
	const ADS_DATA_THRESHOLD = [
		0 => array(
			'ori_slot_type' => '开屏',
			'click_rate' => '14.30',
			'ecpm' => '27',
		),
		1 => array(
			'ori_slot_type' => '插屏',
			'click_rate' => '6.50',
			'ecpm' => '12',
		),
		2 => array(
			'ori_slot_type' => '原生',
			'click_rate' => '1.78',
			'ecpm' => '7.7',
		),
		3 => array(
			'ori_slot_type' => '横幅',
			'click_rate' => '0.80',
			'ecpm' => '2',
		),
	];
	
	public function __construct(){
		parent::__construct();
		$this->load->library('DbUtil');
	}

	/*获取直接判定的异常数据*/
	public function getDirectData($params){
		// id -> 中文
		$this->config->load('style2platform_map');
		$arrStyleMap = $this->config->item('style2platform_map');
		$res = array();

		foreach(self::SLOT_DB_NAME as $key => $value){
			foreach(self::ADS_DATA_THRESHOLD as $k => $v){
				$where = array(
					'select' => 'ori_slot_id,user_slot_id,app_id,acct_id,date,pre_exposure_num,pre_click_num,click_rate,ecpm,pre_profit',
					'where' => '(click_rate>='.$v['click_rate'].' OR ecpm>='.$v['ecpm'].') AND date="'.$params['sampleTime'].'"',
				);
				$tmpRes = $this->dbutil->$value($where);
				foreach($tmpRes as $k1 => $v1){
					$tmpRes[$k1]['ori_slot_source'] = $key;
					$tmpRes[$k1]['click_rate'] = $tmpRes[$k1]['click_rate'].'%';
					$tmpRes[$k1]['delivery_mode'] = $this->getMediaType($v1['app_id']);
					$slotInfo = $this->getSlotInfo($v1['user_slot_id']);
					$tmpRes[$k1]['media_platform'] = $slotInfo['media_platform'];
					$tmpRes[$k1]['slot_style'] = $arrStyleMap[$slotInfo['slot_style']]['des'];
					$tmpRes[$k1]['media_name'] = $slotInfo['media_name'];
					$tmpRes[$k1]['slot_name'] = $slotInfo['slot_name'];
					$tmpRes[$k1]['email'] = $this->getAccountInfo($v1['acct_id']);
					/*
					if($key == 'GDT'){
						$name = iconv("UTF-8","GBK//IGNORE",$v1['ori_slot_name']);
						$type = iconv("UTF-8","GBK//IGNORE",$v1['ori_slot_type']);
						$tmpRes[$k1]['ori_slot_name'] = $name;
						$tmpRes[$k1]['ori_slot_type'] = $type;
					}
					*/	
				}
				$res = array_merge($res,$tmpRes);
				unset($tmpRes);
			}
		}
		if(!$res){
			return [
				'list' => '',
				'pagination' => [
					'total' => 0,
					'pageSize' => (int)$params['pageSize'],
					'current' => (int)$params['currentPage'],
					'sampleTime' => $params['sampleTime'],
				],
			];
		}
		if($params['currentPage'] == 1){
			$list = array_slice($res,0,$params['pageSize']);
		}else{
			$startKey = ($params['currentPage'] - 1) * $params['pageSize'];
			$list = array_slice($res,$startKey,$params['pageSize']);
		}

        return [
            'list' => $list,
            'pagination' => [
                'total' => count($res),
                'pageSize' => (int)$params['pageSize'],
                'current' => (int)$params['currentPage'],
				'sampleTime' => $params['sampleTime'],
            ],
        ];
	}

	/*获取对比异常数据列表*/
	public function getContrastData($params){
		// id -> 中文
		$this->config->load('style2platform_map');
		$arrStyleMap = $this->config->item('style2platform_map');
		$res = array();

		foreach(self::SLOT_DB_NAME as $key => $value){
			$where = array(
				'select' => 'ori_slot_id,user_slot_id,app_id,acct_id,date,pre_exposure_num,pre_click_num,click_rate,ecpm,pre_profit',
				'where' => 'date="'.$params['sampleTime'].'" OR date="'.$params['contrastTime'].'"',
			);
			$tmpRes = $this->dbutil->$value($where);

			foreach($tmpRes as $k1 => $v1){
				$v1['ori_slot_source'] = $key;
				$v1['delivery_mode'] = $this->getMediaType($v1['app_id']);
				$slotInfo = $this->getSlotInfo($v1['user_slot_id']);
				$v1['media_platform'] = $slotInfo['media_platform'];
				$v1['slot_style'] = $arrStyleMap[$slotInfo['slot_style']]['des'];
				$v1['media_name'] = $slotInfo['media_name'];
				$v1['slot_name'] = $slotInfo['slot_name'];
				$v1['email'] = $this->getAccountInfo($v1['acct_id']);
				if($v1['date'] == $params['sampleTime']){
					$res[$params['sampleTime']][$v1['ori_slot_id']] = $v1;
					//$res[$params['sampleTime']][] = $v1;
				}else{
					$res[$params['contrastTime']][$v1['ori_slot_id']] = $v1;
					//$res[$params['contrastTime']][] = $v1;
				}
			}
		}
		if(!$res){
			return [
				'list' => '',
				'pagination' => [
					'total' => 0,
					'pageSize' => (int)$params['pageSize'],
					'current' => (int)$params['currentPage'],
					'sampleTime' => $params['sampleTime'],
					'contrastTime' => $params['contrastTime'],
				],
			];
		}

		foreach($res[$params['sampleTime']] as $key => $value){
			$slotType = array('开屏','插屏','原生','横幅');
			if(!in_array($value['slot_style'],$slotType)){
				continue;
			}

			if($res[$params['contrastTime']][$key]){
				$toDayEcpm = $value['ecpm'];
				$yesToDayEcpm = $res[$params['contrastTime']][$key]['ecpm'];
				$toDayExposure = $value['pre_exposure_num'];
				$yesToDayExposure = $res[$params['contrastTime']][$key]['pre_exposure_num'];
				//$toDayClickRate = $value['click_rate'];
				//$yesToDayClickRate = $res[$params['contrastTime']][$key]['click_rate']
				//$exposureIncrease = $toDayExposure > $yesToDayExposure ? (sprintf("%.2f", round(($toDayExposure - $yesToDayExposure) / ($yesToDayExposure+1) * 100 ,2)) >= 20 ? true : false) : false;
				$exposureIncrease = sprintf("%.2f", round(($toDayExposure - $yesToDayExposure) / ($yesToDayExposure ? $yesToDayExposure : 1) * 100 ,2));

				$res[$params['sampleTime']][$key]['contrast_exposure_num'] = $yesToDayExposure;
				$res[$params['sampleTime']][$key]['increase_exposure_rate'] = $exposureIncrease;
				
				if($exposureIncrease < 20){
					$exposureIncrease = false;
				}
				
				/*
				switch($value['slot_style']){
					case "开屏":
						$clickRate = $value['click_rate'] >= 14.30 ? true : false;
						$ecpm = $value['ecpm'] >= 27 ? true : false;
						break;
					case "插屏":
						$clickRate = $value['click_rate'] >= 6.50 ? true : false;
						$ecpm = $value['ecpm'] >= 12 ? true : false;
						break;
					case "原生":
						$clickRate = $value['click_rate'] >= 1.78 ? true : false;
						$ecpm = $value['ecpm'] >= 7.7 ? true : false;
						break;
					case "横幅":
						$clickRate = $value['click_rate'] >= 0.80 ? true : false;
						$ecpm = $value['ecpm'] >= 2 ? true : false;
						break;
				}
				 */

				//if($exposureIncrease || $clickRate || $ecpm){
				if($exposureIncrease){
					$res[$params['sampleTime']][$key]['increase_exposure_rate'] = $res[$params['sampleTime']][$key]['increase_exposure_rate'].'%';
					$res[$params['sampleTime']][$key]['click_rate'] = $res[$params['sampleTime']][$key]['click_rate'].'%';

					$result[$params['sampleTime']][] = $res[$params['sampleTime']][$key];
					//$result[$params['contrastTime']][] = $res[$params['contrastTime']][$key];
				}
			}
		}
		if(!$result){
			return false;
		}
		
		if($params['currentPage'] == 1){
			$list = array_slice($result[$params['sampleTime']],0,$params['pageSize']);
			//$list[$params['sampleTime']] = array_slice($result[$params['sampleTime']],0,$params['pageSize']);
			//$list[$params['contrastTime']] = array_slice($result[$params['contrastTime']],0,$params['pageSize']);
			//$list[0] = array_slice($result[$params['sampleTime']],0,$params['pageSize']);
			//$list[1] = array_slice($result[$params['contrastTime']],0,$params['pageSize']);
		}else{
			$startKey = ($params['currentPage'] - 1) * $params['pageSize'];
			$list = array_slice($result[$params['sampleTime']],$startKey,$params['pageSize']);
			//$list[$params['sampleTime']] = array_slice($result[$params['sampleTime']],$startKey,$params['pageSize']);
			//$list[$params['contrastTime']] = array_slice($result[$params['contrastTime']],$startKey,$params['pageSize']);
			//$list[0] = array_slice($result[$params['sampleTime']],$startKey,$params['pageSize']);
			//$list[1] = array_slice($result[$params['contrastTime']],$startKey,$params['pageSize']);
		}

        return [
            'list' => $list,
            'pagination' => [
                'total' => count($result[$params['sampleTime']]),
				'pageSize' => (int)$params['pageSize'],
				'current' => (int)$params['currentPage'],
				'sampleTime' => $params['sampleTime'],
				'contrastTime' => $params['contrastTime'],
			],
        ];

	}

	public function getAccountInfo($accId){
		$where = array(
			'select' => 'email',
			'where' => 'account_id="'.$accId.'"',
		);
		$res = $this->dbutil->getAccount($where);
		return $res[0]['email'];
	}

	public function getMediaType($appId){
		$where  = array(
			'select' => 'media_delivery_method',
			'where' => 'app_id="'.$appId.'"',
		);
		$res = $this->dbutil->getMedia($where);
		return $res[0]['media_delivery_method'];
	}

	public function getSlotInfo($slotId){
		$where = array(
			'select' => 'media_platform,slot_style,media_name,slot_name',
			'where' => 'slot_id='.$slotId,
		);
		$res = $this->dbutil->getAdslot($where);
		return $res[0];
	}
}

?>
