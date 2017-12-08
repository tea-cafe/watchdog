<?php
class MediaCheckStatus extends BG_Controller {

	public function __construct(){
		parent::__construct();
	}

    /**
     * 编辑上传H5 app_key后点击完成
     * H5 专用， check_status 0 => 1
     * H5 1-0-2-3/4
     * APP 0-2-3/4
     */
    public function index() {
        if (empty($this->arrUser)) {
            return $this->outJson('', ErrCode::ERR_NOT_LOGIN); 
        }
        $arrPostParams = json_decode(file_get_contents('php://input'), true);
        if (empty($arrPostParams['app_id'])
            || empty($arrPostParams['check_status'])) {
            return $this->outJson('', ErrCode::ERR_INVALID_PARAMS); 
        }

        // action =0 审核不通过  action = 1 审核通过
        switch (intval($arrPostParams['check_status'])) {
            case 1: // H5类型，编辑上传app_key文件后，状态由 1 => 0 
                if (isset($arrPostParams['action'])
                    && $arrPostParams['action'] === 0) {
                    $intStatus = 4;
                } else {
                    $intStatus = 0;
                }
                break;
            case 2:
                if (isset($arrPostParams['action'])
                    && $arrPostParams['action'] === 0) {
                    $intStatus = 4;
                } else {
                    $intStatus = 3;
                }
                break;
            default:
                return $this->outJson('', ErrCode::ERR_INVALID_PARAMS);
        }

        $arrUpdate = [
            'check_status' => $intStatus,
            'where' => "app_id='" . $arrPostParams['app_id'] . "'",
        ];

        // H5媒体注册手状态是1，编辑会上传app_key然后checkstatus，此时会提交上来app_key的地址
        if (intval($arrPostParams['check_status']) === 1) {
            $arrUpdate['app_verify_url'] = $arrPostParams['app_verify_url']; 
        }

        $this->load->model('MediaManager');
        $bolRes = $this->MediaManager->updateMediaInfo($arrUpdate);
        if (!$bolRes) {
            return $this->outJson('', ErrCode::ERR_SYSTEM, '操作失败');
        }
        return $this->outJson('', ErrCode::OK);
    }
}
