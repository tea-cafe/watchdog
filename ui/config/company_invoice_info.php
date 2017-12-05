<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 发票信息和邮寄信息
 */

$config['invoice'] = array(
	'info' => array(
		'company_name' => '杭州推啊网络科技有限公司',
		'company_address' => '杭州市西湖区文一西路98号数娱大厦808室',
		'phone' => '0571-28258680',
		'recognition number' => '91330106MA27YMR20H',
		'bank' => '华夏银行杭州高新支行',
		'bank_account' => '10454000000505349',
		'type' => '增值税专用发票',
	),
	'mail' => array(
		'address' => '杭州市西湖区文一西路98号数娱大厦702室',
		'postcodes' => '310000',
		'addressee' => '媒介',
		'phone' => '0571-28258680',
	),
);

?>
