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
    public function BAI() {
        $arrData = $this->CsvAdapter->baidu();

        $this->outJson($arrData, ErrCode::OK);
    }

}
