<?php
class Reports extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->library('DbUtil');
    }


    public function getViewList($arrParams) {//{{{//
        $intCount = 0;
        if(!isset($arrParams['count']) || $arrParams['count'] == 0) {
            $intCount = $this->getTotalCount($arrParams);
        }
        $rn = $arrParams['rn'];
        $pn = $arrParams['pn'];
		if(!isset($arrParams['sorter'])){
			$arrParams['sorter'] = 'pre_exposure_num_descend';
		}
		
		if(strpos($arrParams['sorter'],'descend')){
			$orderBy = str_replace('_descend','',$arrParams['sorter']).' desc';
		}elseif(strpos($arrParams['sorter'],'ascend')){
			$orderBy = str_replace('_ascend','',$arrParams['sorter']).' asc';
		}

		$arrSelect = [
            'select' => '*',
            'where' => " date='" .$arrParams['date']. "'",
            'order_by' => $orderBy,
            'limit' => $rn*($pn-1) . ',' . $rn,
		];
		
		$method = $arrParams['method'];
        $arrRes = $this->dbutil->$method($arrSelect);
		if(empty($arrRes[0])) {
			$result = [
                'list' => [],
                'pagination' => [
                    'total' => $intCount,
                    'pageSize' => (int)$rn,
					'current' => (int)$pn,
					'date' => $arrParams['date'],
					'sorter' => $arrParams['sorter'],
                ],
			];

			if(isset($arrParams['source'])){
				$result['pagination']['source'] = $arrParams['source'];
			}else{
				$result['pagination']['type'] = $arrParams['type'];
			}
			return $result;
        }

		$result = [
            'list' => $arrRes,
            'pagination' => [
                'total' => $intCount,
                'pageSize' => (int)$rn,
				'current' => (int)$pn,
				'date' => $arrParams['date'],
				'sorter' => $arrParams['sorter'],
			],
		];
		
		if(isset($arrParams['source'])){
			$result['pagination']['source'] = $arrParams['source'];
		}else{
			$result['pagination']['type'] = $arrParams['type'];
		}

		return $result;
    }//}}}//

    private function getTotalCount($arrParams) {//{{{//
        $arrSelect = [
            'select' => 'count(*) as total',
            'where' => "date='" .$arrParams['date']. "'",
        ];
        $method = $arrParams['method'];
        $arrRes = $this->dbutil->$method($arrSelect);
        $intCount = $arrRes[0] ? intval($arrRes[0]['total']) : 0;
        return $intCount;
    }//}}}//

}
