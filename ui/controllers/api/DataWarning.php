<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 后台 获取预警数据
 */

class DataWarning extends BG_Controller {
	public function __construct(){
		parent::__construct();
        $this->load->model('DataWarningManager');
	}

	public function AbsoluteValue(){
        if(empty($this->arrUser)){
            return $this->outJson('',ErrCode::ERR_NOT_LOGIN);
        }
		$params['sampleTime'] = $this->input->get('sampleTime');
		$params['pageSize'] = $this->input->get('pageSize');
		$params['currentPage'] = $this->input->get('currentPage');

		$params['pageSize'] = $params['pageSize'] ? $params['pageSize'] : 10;
		$params['currentPage'] = $params['currentPage'] ? $params['currentPage'] : 1;
		
		if(!strtotime($params['sampleTime'])){
			$params['sampleTime'] = date("Y-m-d");
		}
		
		//获取直接判定的异常数据
		$res = $this->DataWarningManager->getDirectData($params);
		if(empty($res)){
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'数据查询失败');
		}

		return $this->outJson($res,ErrCode::OK,'数据查询成功');
	}

	public function RingRatio(){
        if(empty($this->arrUser)){
            return $this->outJson('',ErrCode::ERR_NOT_LOGIN);
        }
		$params['sampleTime'] = $this->input->get('sampleTime');
		$params['contrastTime'] = $this->input->get('contrastTime');
		$params['pageSize'] = $this->input->get('pageSize');
		$params['currentPage'] = $this->input->get('currentPage');

		$params['pageSize'] = $params['pageSize'] ? $params['pageSize'] : 10;
		$params['currentPage'] = $params['currentPage'] ? $params['currentPage'] : 1;
		
		if(!strtotime($params['sampleTime'])){
			$params['sampleTime'] = date("Y-m-d");
			$yestoday = $params['sampleTime'];
			$params['contrastTime'] = date("Y-m-d",strtotime("$yestoday -1 day"));
		}elseif(!strtotime($params['contrastTime'])){
			$yestoday = $params['sampleTime'];
			$params['contrastTime'] = date("Y-m-d",strtotime("$yestoday -1 day"));
		}

		$res = $this->DataWarningManager->getContrastData($params);
		if(empty($res)){
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'数据查询失败');
		}

		return $this->outJson($res,ErrCode::OK,'数据查询成功');
	}
}
?>
