<?php
/**
 * 提现审核
 */

class BgTakeMoney extends MY_Controller{
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
		$orderNumber = $this->input->post('order_number',true);
		if(empty($orderNumber)){
			return $this->outJson('', ErrCode::ERR_INVALID_PARAMS);
		}

		$this->load->model('bg/TakeMoney');
		$res = $this->TakeMoney->getInfo($orderNumber);
		
		if(empty($res)){
			return $this->outJson('', ErrCode::ERR_INVALID_PARAMS);
		}else{
			return $this->outJson($res, ErrCode::OK,'获取提现单信息成功');
		}
	}

	/*审核提现订单*/
	public function examine(){
		$data['order_number'] = $this->input->post('order_number',true);
		$data['money'] = $this->input->post('money',true);
		$data['code'] = $this->input->post('code',true);
		$data['photo'] = $this->input->post('photo',true);
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
		
		$this->load->model('bg/TakeMoney');
		$res = $this->TakeMoney->modifyInfo($data['order_number'],$data,$status,$remark);
		
		if($res){
			return $this->outJson('', ErrCode::OK,'审核通过');
		}else{
			return $this->outJson('', ErrCode::ERR_INVALID_PARAMS,'审核失败,请重新审核');
		}
	}

	/*上传发票照片*/
	public function UpInvoicePhoto(){
		header("Content-Type:application/json");
		$newName = '/invoice_'.time().mt_rand(100,999).str_replace('image/','.',$_FILES['file']['type']);
		$newDir = 'Uploads/images/'.date('Ym');
		$newPath = FCPATH.$newDir;

		if(!is_dir($newPath)){
			@mkdir($newPath);
		}
		
		$res = move_uploaded_file($_FILES['file']['tmp_name'],$newPath.$newName);
		
		if($res){
			$data['img_url'] = '/'.$newDir.$newName;
			return $this->outJson($data,ErrCode::OK,'发票图片上传成功');
		}else{
			return $this->outJson('', ErrCode::ERR_INVALID_PARAMS,'发票上传失败');
		}
	}
}

?>
