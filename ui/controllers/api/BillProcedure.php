<?php
class BillProcedure extends BG_Controller {
        public function __construct() {
            parent::__construct();
            $this->load->model('AccountBalanceManager');
        }

        /**
         * 操作写入账户余额
         */
        public function mergeAccountBalance() {
            //if (empty($this->arrUser)) {
            //    return $this->outJson('', ErrCode::ERR_NOT_LOGIN);
            //}
            $intAction = $this->AccountBalanceManager->getAccountBalanceAction();
            if ($intAction !== 0) {
                $this->outJson('', ErrCode::ERR_SYSTEM, '上月账单已合如余额表');
            }
            $bolRes = $this->doMergeAccountBalance();
            if ($bolRes) {
                return $this->outJson('', ErrCode::ERR_SYSTEM);
            }
            return $this->outJson('', ErrCode::OK);

        }

        /**
         * 回滚
         *
         */
        public function rollbackAccountBalance() {
            //if (empty($this->arrUser)) {
            //    return $this->outJson('', ErrCode::ERR_NOT_LOGIN);
            //}
            $intAction = $this->AccountBalanceManager->getAccountBalanceAction();
            if ($intAction !== 1) {
                $this->outJson('', ErrCode::ERR_SYSTEM, '上月账单未入库，不能回滚');
            }
            $bolRes = $this->doRollbackAccountBalance();
            if ($bolRes) {
                return $this->outJson('', ErrCode::ERR_SYSTEM);
            }
            return $this->outJson('', ErrCode::OK, '回滚成功');

            

        }

}
