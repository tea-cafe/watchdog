<?php
/**
 * 用户注册接口
 * szishuo
 */
class AccountRegister extends MY_Controller {

    const VALID_ACCOUNT_BASE_KEY = [
        'email', 
        'passwd',
        'confirm',
        'phone', 
        'company',
        'contact_person',
    ]; 
        
    const VALID_ACCOUNT_COMPANY_FINANCE_KEY = [
        'financial_object',
        'contact_address',
        'bussiness_license_num',
        'bussiness_license_pic',
        'account_open_permission',
		'collection_company',
		'bank',
        'account_holder',
        'city',  
        'bank_branch',
        'bank_account',
        'remark',
    ]; 

    const VALID_ACCOUNT_PERSIONAL_FINANCE_KEY = [
        'financial_object',
        'contact_address',
        'account_holder',
        'identity_card_num',
        'identity_card_front',
        'identity_card_back',
        'bank',
        'city',  
        'bank_branch',
        'bank_account',
        'remark', 
    ]; 

    public function __construct() {
        parent::__construct();
    }

    /**
     * 账号注册
     */
    public function index() {//{{{//
        $arrPostParams = json_decode(file_get_contents('php://input'), true);
        if (empty($arrPostParams)) {
            return $this->outJson('', ErrCode::ERR_INVALID_PARAMS); 
        }

        // TODO 各种号码格式校验
        foreach ($arrPostParams as $key => &$val) {
            if(!in_array($key, self::VALID_ACCOUNT_BASE_KEY)) {
                return $this->outJson('', ErrCode::ERR_INVALID_PARAMS); 
            }
            $val = $this->security->xss_clean($val);
        }
        $arrPostParams['passwd'] = md5($arrPostParams['passwd']);
        unset($arrPostParams['confirm']);
        
        // 入库
        $this->load->model('Account');
        $arrRes = $this->Account->insertAccountBaseInfo($arrPostParams);

        if ($arrRes['code'] === 0) {
            $this->load->model('User');
            $this->User->doLogin($arrPostParams['email'], md5($arrPostParams['passwd']));
            return $this->outJson('', ErrCode::OK, '注册成功');
        }
        if ($arrRes['code'] === 1062) {
            return $this->outJson('', ErrCode::ERR_DUPLICATE_ACCOUNT);
        }
        return $this->outJson('', ErrCode::ERR_SYSTEM);
    }//}}}//
}
