<?php
include('/home/work/anteater/ui/config/slot_source_map.php');
$arrSoutceMap = $config['source_map'];

$sql = 'INSERT INTO adslot_style_info(slot_type,slot_style,size,proportion) VALUES';

$tmp = '';
foreach ($arrSoutceMap as $type => $arrStyle) {
    foreach ($arrStyle as $style => $arrVal) {
        foreach($arrVal['size'] as $size) {
    $tmp = "('" . $type . "','" . $style . "','" . $size . "','" . json_encode($arrVal['proportion']) . "'),"; 
            $sql .= $tmp;
            $tmp = '';
        }         
    }
}
$sql = substr($sql, 0, -1);


$host = '10.99.202.57';
$port = 3306;
$user = 'hao123';
$pass = 'hao123';
$db = 'szsdb';
$mysqli = new mysqli($host,$user,$pass,$db, $port);
$result = $mysqli->query($sql);
var_dump($result);
