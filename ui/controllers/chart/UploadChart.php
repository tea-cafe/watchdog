<?php
class UploadChart extends BG_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper(['form', 'url']);
        $this->load->model('chart/CsvAdapter');
        if (empty($this->arrUser)) {
            return $this->outJson([], ErrCode::ERR_NOT_LOGIN);
        }
    }

    public function index() {
        $this->load->view('upload_csv', array('error' => ''));
    }

    /**
     * @param void
     * @return void
     */
    public function _remap($method, $params = []) {
		if (method_exists($this, $method)) {
			$reflection = new ReflectionMethod($this, $method);
            return $this->$method($params);
		}
        return $this->index();
    }

    /**
     * @param void
     * @return void
     */
    public function BAIDU($arrParams) {//{{{//
        $arrParams = $this->input->get(NULL, TRUE);
        //$arrParams['date'] = '2017-10-01';
        //$arrParams['source'] = 'BAIDU';

        if(empty($arrParams['date'])
            || empty($arrParams['source'])) {
            return $this->outJson([], ErrCode::ERR_INVALID_PARAMS);
        }
        $arrData = $this->CsvAdapter->process($arrParams);

        return $arrData ? $this->outJson($arrData, ErrCode::OK) : $this->outJson($arrData, ErrCode::ERR_UPLOAD);
    }//}}}//

    /**
     * @param void
     * @return void
     */
    public function GDT($arrParams) {//{{{//
        $arrParams = $this->input->get(NULL, TRUE);
        if(empty($arrParams['date'])
            || empty($arrParams['source'])) {
            return $this->outJson([], ErrCode::ERR_INVALID_PARAMS);
        }
        $arrData = $this->CsvAdapter->process($arrParams);

        return $arrData ? $this->outJson($arrData, ErrCode::OK) : $this->outJson($arrData, ErrCode::ERR_UPLOAD);
    }//}}}//

    /**
     * @param void
     * @return void
     */
    public function TUIA($arrParams) {//{{{//
        $arrParams = $this->input->get(NULL, TRUE);
        if(empty($arrParams['date'])
            || empty($arrParams['source'])) {
            return $this->outJson([], ErrCode::ERR_INVALID_PARAMS);
        }
        $arrData = $this->CsvAdapter->process($arrParams);

        return $arrData ? $this->outJson($arrData, ErrCode::OK) : $this->outJson($arrData, ErrCode::ERR_UPLOAD);
    }//}}}//

    /**
     * @param void
     * @return void
     */
    public function YEZI($arrParams) {//{{{//
        $arrParams = $this->input->get(NULL, TRUE);

        if(empty($arrParams['date'])
            || empty($arrParams['source'])) {
            return $this->outJson([], ErrCode::ERR_INVALID_PARAMS);
        }
        $arrData = $this->CsvAdapter->process($arrParams);

        return $arrData ? $this->outJson($arrData, ErrCode::OK) : $this->outJson($arrData, ErrCode::ERR_UPLOAD);
    }//}}}//
}
