<?php
class Test extends MY_Controller {

    public function __construct() {
        parent::__construct();
    }
    
    public function index() {
        $this->load->model('SyncSdkMediaInfo');
        list($app_id,$slot_id,$slot_style,$arrUpstreamSlotIdsForApp) = json_decode(file_get_contents('/home/work/test.json'), true);
        $this->SyncSdkMediaInfo->syncWhenAdSlotIdRegist($app_id,$slot_id,$slot_style,$arrUpstreamSlotIdsForApp);

    }

}
