<?php
	function keys($length){
		$key = '';
		$pattern = 'abcdefghijklmnopqrstuvwxyz1234567890ABCDEFGHIJKLOMNOPQRSTUVWXYZ';
		for ($i = 0; $i < $length; $i++) {
			$key .= $pattern{mt_rand(0, 61)}; //生成php随机数
		}
		return $key;
	}
?>
