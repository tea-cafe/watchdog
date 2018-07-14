<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 后台 渠道(用户)管理
 */

class Charging extends BG_Controller {
	public function __construct(){
		parent::__construct();
        $this->load->model('ChargingManager');
	}

	/**
	 * @获取申请计费名的渠道列表
	 */
	public function channelList(){
        if(empty($this->arrUser)){
            return $this->outJson('',ErrCode::ERR_NOT_LOGIN);
        }
        $arrParams = $this->input->get(NULL,TRUE);
		
        $arrParams['pageSize'] = isset($arrParams['pageSize']) ? $arrParams['pageSize'] : 10;
		$arrParams['current'] = isset($arrParams['current']) ? $arrParams['current'] : 1;
		
		if(!isset($arrParams['channelName']) || empty($arrParams['channelName'])){
			$arrParams['channelName'] = 'all';
		}

		$res = $this->ChargingManager->getChannelList($arrParams);
		
		if(!$res){
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'渠道列表查询失败');
		}

		return $this->outJson($res,ErrCode::OK,'渠道列表查询成功');
	}

	/**
	 * @获取申请计费名的渠道信息
	 */
	public function getChannelInfo(){
		if(empty($this->arrUser)){
			return $this->outJson('',ErrCode::ERR_NOT_LOGIN);
		}
		$arrParams = $this->input->get(NULL,TRUE);

		if(!isset($arrParams['account_id']) || empty($arrParams['account_id'])){
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'渠道信息查询失败');
		}

		$res = $this->ChargingManager->getChannelInfo($arrParams['account_id']);
		if(!$res){
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'渠道信息查询失败');
		}

		return $this->outJson($res[0],ErrCode::OK,'渠道列表查询成功');
	}

	/**
	 * @计费名申请审核
	 */
	public function ChannelCheck(){
		if(empty($this->arrUser)){
			return $this->outJson('',ErrCode::ERR_NOT_LOGIN);
		}
		$arrParams = $this->input->get(NULL,TRUE);
		
		$res = $this->ChargingManager->ChannelCheck($arrParams);
		if(!$res){
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'审核失败,可能已经审核');
		}

		return $this->outJson($res,ErrCode::OK,'审核完成');
	}

	/**
	 * @计费名增加
	 */
	public function ChargingAdd(){
		if(empty($this->arrUser)){
			return $this->outJson('',ErrCode::ERR_NOT_LOGIN);
		}
		$arrParams = $this->input->get(NULL,TRUE);
		if($arrParams['charging_name'] == ''
			|| empty($arrParams['charging_name'])
		){
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'添加失败,计费名不合规范');
		}

		$res = $this->ChargingManager->ChargingAdd($arrParams);

		if(!$res){
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'添加失败,审核未通过或者重复添加');
		}

		return $this->outJson($res,ErrCode::OK,'添加成功');
	}

	public function DailyData(){
        if(empty($this->arrUser)){
            return $this->outJson('',ErrCode::ERR_NOT_LOGIN);
        }
        $arrParams = $this->input->get(NULL,TRUE);

        $arrParams['pageSize'] = isset($arrParams['pageSize']) ? $arrParams['pageSize'] : 10;
		$arrParams['current'] = isset($arrParams['current']) ? $arrParams['current'] : 1;

		if(!isset($arrParams['date']) || empty($arrParams['date'])){
			$time = date("Y-m-d");
			$arrParams['date'] = date("Y-m-d",strtotime("$time -1 day"));
		}
		$res = $this->ChargingManager->getDailyData($arrParams);
		
		if(empty($res)){
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'渠道列表查询失败');
		}

		return $this->outJson($res,ErrCode::OK,'渠道列表查询成功');
	}

	/**
	 * @获取效果报告
	 */
	public function reportData(){
		if(empty($this->arrUser)){
			return $this->outJson('',ErrCode::ERR_NOT_LOGIN);
		}
		
		$arrParams = $this->input->get(NULL,TRUE);
		$arrParams['pageSize'] = isset($arrParams['pageSize']) ? $arrParams['pageSize'] : 10;
		$arrParams['current'] = isset($arrParams['current']) ? $arrParams['current'] : 1;
		
		if(!isset($arrParams['startDate']) || empty($arrParams['startDate'])){
			$time = date("Y-m-d");
			$arrParams['startDate'] = date("Y-m-d",strtotime("$time -31 day"));
			$arrParams['endDate'] = date("Y-m-d",strtotime("$time -1 day"));
		}
		$res = $this->ChargingManager->getReportData($arrParams);

		if(empty($res)){
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'查询失败');
		}

		return $this->outJson($res,ErrCode::OK,'获取数据成功');
	}

	/**
	 * @更改导入数据状态
	 */
	public function importDataMark(){
		if(empty($this->arrUser)){
			return $this->outJson('',ErrCode::ERR_NOT_LOGIN);
		}

		$arrParams = $this->input->get(NULL,TRUE);
		$res = $this->ChargingManager->modifyImportDataMark($arrParams);
		if(!$res){
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'操作失败');
		}

		return $this->outJson($res,ErrCode::OK,'操作成功');
	}
}
?>
