<?php
/**
 * 提现审核
 */

class TakeMoney extends BG_Controller{
	const VALID_INVOICE_INFO_KEY = [
		'order_number',
		'money',
		'code',
		'photo',
		'fill_in_money',
		'should_fill_in_money',
		'number',
	];

	public function __construct(){
		parent::__construct();
	}

	/*获取提现订单信息*/
	public function index(){
        if(empty($this->arrUser)){
            return $this->outJson('',ErrCode::ERR_NOT_LOGIN);
		}

		$orderNumber = $this->input->get('order_number',true);
		if(empty($orderNumber)){
			return $this->outJson('', ErrCode::ERR_INVALID_PARAMS);
		}

		$this->load->model('TakeMoneyManager');
		$res = $this->TakeMoneyManager->getInfo($orderNumber);
		
		if(empty($res)){
			return $this->outJson('', ErrCode::ERR_INVALID_PARAMS);
		}else{
			return $this->outJson($res, ErrCode::OK,'获取提现单信息成功');
		}
	}

	/**
	 * 审核提现订单
	 * 发票可能为多张,存为数组
	 */

	public function examine(){
        if(empty($this->arrUser)){
            return $this->outJson('',ErrCode::ERR_NOT_LOGIN);
		}

		$data['order_number'] = $this->input->post('order_number',true);
		$data['money'] = $this->input->post('money',true);
		$data['code'] = $this->input->post('code',true);
		$data['photo'][0] = $this->input->post('photo',true);
		$data['fillInMoney'] = $this->input->post('fill_in_money',true);
		$data['shouldFillInMoney'] = $this->input->post('should_fill_in_money',true);
		$data['number'] = $this->input->post('number',true);
		$status = $this->input->post('status',true);
		$remark = $this->input->post('remark',true);

		foreach($data as $k => $v){
			if(empty($v)){
				return $this->outJson('', ErrCode::ERR_INVALID_PARAMS);
			}
		}
		
		if($status == '3' && empty($remark)){
			return $this->outJson('', ErrCode::ERR_INVALID_PARAMS,'未填写审核失败原因');
		}

		$this->config->load('company_invoice_info');
		$company_invoice_info = $this->config->item('invoice');
		
		$this->load->model('TakeMoneyManager');
		$res = $this->TakeMoneyManager->modifyInfo($data['order_number'],$data,$status,$remark);
		if($res){
			return $this->outJson('', ErrCode::OK,'审核完成');
		}else{
			return $this->outJson('', ErrCode::ERR_INVALID_PARAMS,'审核失败,请重新审核');
		}
	}

	public function confirmRemitMoney(){
        if(empty($this->arrUser)){
            return $this->outJson('',ErrCode::ERR_NOT_LOGIN);
		}

		$orderNumber = $this->input->get('order_number',true);
		if(empty($orderNumber) || strlen($orderNumber) != 15){
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'参数错误');
		}

		$this->load->model('TakeMoneyManager');
		$res = $this->TakeMoneyManager->remitMoney($orderNumber);
		if($res){
			return $this->outJson('',ErrCode::OK,'打款状态成功');
		}else{
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'打款状态失败');
		}
	}

	/*上传发票照片*/
	public function UpInvoicePhoto(){
        if(empty($this->arrUser)){
            return $this->outJson('',ErrCode::ERR_NOT_LOGIN);
		}

        $this->load->library('UploadTools');
        $arrUdpImgConf = $this->config->item('img');
		$newName = '/invoice_'.time().mt_rand(100,999).str_replace('image/','.',$_FILES['file']['type']);
        $arrUdpImgConf['file_name'] = $newName; 

        $strUrl = $this->uploadtools->upload($arrUdpImgConf);
		
        if (empty($strUrl)) {
            return $this->outJson('', ErrCode::ERR_UPLOAD, '发票上传失败，请重试');
        }
        return $this->outJson(
            ['url' => $strUrl],
            ErrCode::OK,
            '发票上传成功'
        );
	}
}

?>
