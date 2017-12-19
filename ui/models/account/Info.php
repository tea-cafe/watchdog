<?php
/**
 * getInfo 账户信息
 *
 */
class Info extends Model {
    

    public function __construct() {
        parent::__construct();

    }

    /**
     * 获取媒体信息
     *
     * @return array
     */
    public function getInfo() {
        $strJsonTest = '{"code":"0","desc":"成功","data":{"mediaId":23409,"email":"gongwei@ayang.com","companyName":"北京云保网络科技有限公司","linkman":"龚唯","financeCompanyName":"北京云保网络科技有限公司","businessLicenseId":"91110105MA001TCC6M","businessLicenseName":null,"businessLicenseUrl":"//yun.duiba.com.cn/tuia-media/img/u13r2h7wwj.jpg","linkPhone":"15510797564","idCard":null,"cardNumber":"8110701012101135292","bankName":"中信银行望京支行","province":"北京市","city":"110105","branchName":"中信银行望京支行","roleType":0,"personalName":null,"idCardFrontUrl":null,"idCardBackUrl":null,"checkMsg":null,"checkStatus":2,"companyAddr":"北京市朝阳区融科望京中心A座801","linkmanAddr":"北京市朝阳区融科望京中心A座801","accountOpenman":"北京云保网络科技有限公司","accountOpenbookUrl":"//yun.duiba.com.cn/tuia-media/img/d6use5yqqm.jpg","noteInformationUrl":null,"resubmitReason":null,"isFirstSubmit":0,"unlockStatus":0,"accessDataStatus":0,"edited":true}}';
        return json_decode($strJsonTest, true);
    } 
}
