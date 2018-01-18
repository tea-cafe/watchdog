<?php
/**
 * 提现审核
 */

class TakeMoney extends BG_Controller{
    /*
    const VALID_INVOICE_INFO_KEY = [
		'order_number',
		'money',
		'code',
		'photo',
		'fill_in_money',
		'should_fill_in_money',
		'number',
	];
    */

	public function __construct(){
		parent::__construct();
        $this->load->model('TakeMoneyManager');
	}

    public function index(){
        if(empty($this->arrUser)){
            return $this->outJson('',ErrCode::ERR_NOT_LOGIN);
		}

        $pageSize = $this->input->get("pagesize",true);
        $currentPage = $this->input->get("currentpage",true);
        $keyWord = $this->input->get('number',true);
        
        if(empty($pageSize) || empty($currentPage)){
			$pageSize = 20;
			$currentPage = 1;
		}

        $res = $this->TakeMoneyManager->getList($keyWord,$pageSize,$currentPage);

        if(empty($res)){
			return $this->outJson('', ErrCode::ERR_INVALID_PARAMS);
        }

        if(empty($res['list'])){
            return $this->outJson($res,ErrCode::OK,'暂无提现记录');
        }else{
            return $this->outJson($res,ErrCode::OK,'获取提现记录成功');
        }
    }

    /**
     * 审核详情页
     */
	public function content(){
        if(empty($this->arrUser)){
            return $this->outJson('',ErrCode::ERR_NOT_LOGIN);
		}

		$orderNumber = $this->input->get('order_number',true);
		if(empty($orderNumber)){
			return $this->outJson('', ErrCode::ERR_INVALID_PARAMS);
		}

		$res = $this->TakeMoneyManager->getInfo($orderNumber);
		
        if(empty($res)){
			return $this->outJson('', ErrCode::ERR_INVALID_PARAMS);
        }else{
			return $this->outJson($res, ErrCode::OK,'获取提现单列表成功');
		}
	}

	/**
	 * 审核提现订单
	 */

	public function examine(){
        if(empty($this->arrUser)){
            return $this->outJson('',ErrCode::ERR_NOT_LOGIN);
		}
        
        $arrPostParams = json_decode(file_get_contents('php://input'), true);
        $orderNumber = $arrPostParams['order_number'];
		$status = $arrPostParams['status'];
        $remark = $arrPostParams['remark'];
        $action = $arrPostParams['action'];

		if($action == 0 && empty($remark)){
			return $this->outJson('', ErrCode::ERR_INVALID_PARAMS,'未填写审核失败原因');
		}

		$this->config->load('company_invoice_info');
		$company_invoice_info = $this->config->item('invoice');
		
		$res = $this->TakeMoneyManager->modifyInfo($orderNumber,$action,$status,$remark);

        if($res == 2){
			return $this->outJson('', ErrCode::OK,'审核已完成,请勿重复操作');
        }

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
