<?php
class UploadChart extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper(['form', 'url']);
        $this->load->model('chart/CsvAdapter');
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
        //$arrParams = $this->input->post(NULL, TRUE);
        $arrParams['date'] = '2017-12-06';
        //$arrParams['source'] = __FUNCTION__;
        $arrParams['source'] = 'BAIDU';
        $arrData = $this->CsvAdapter->process($arrParams);

        return $arrData ? $this->outJson($arrData, ErrCode::OK) : $this->outJson($arrData, ErrCode::ERR_UPLOAD);
    }//}}}//

    /**
     * @param void
     * @return void
     */
    public function GDT($arrParams) {//{{{//
        $arrParams = $this->input->get(NULL, TRUE);
        //$arrParams = $this->input->post(NULL, TRUE);
        $arrParams['date'] = '2017-12-06';
        $arrParams['source'] = 'GDT';
        $arrData = $this->CsvAdapter->process($arrParams);

        return $arrData ? $this->outJson($arrData, ErrCode::OK) : $this->outJson($arrData, ErrCode::ERR_UPLOAD);
    }//}}}//

    /**
     * @param void
     * @return void
     */
    public function TUIA($arrParams) {//{{{//
        $arrParams = $this->input->get(NULL, TRUE);
        //$arrParams = $this->input->post(NULL, TRUE);
        $arrParams['date'] = '2017-12-06';
        $arrParams['source'] = 'TUIA';
        $arrData = $this->CsvAdapter->process($arrParams);

        return $arrData ? $this->outJson($arrData, ErrCode::OK) : $this->outJson($arrData, ErrCode::ERR_UPLOAD);
    }//}}}//

    /**
     * @param void
     * @return void
     */
    public function YEZI($arrParams) {//{{{//
        $arrParams = $this->input->get(NULL, TRUE);
        //$arrParams = $this->input->post(NULL, TRUE);
        $arrParams['date'] = '2017-12-06';
        //$arrParams['source'] = __FUNCTION__;
        $arrParams['source'] = 'YEZI';
        $arrData = $this->CsvAdapter->process($arrParams);

        return $arrData ? $this->outJson($arrData, ErrCode::OK) : $this->outJson($arrData, ErrCode::ERR_UPLOAD);
    }//}}}//
}
