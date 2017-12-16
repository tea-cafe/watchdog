<?php

// 给个 app_id 就能插入了
$app_id='0ceba39';

$arrUpstream = ['BAIDU','TUIA','GDT','YEZI'];
$arrType = ['SDK','API','JS'];
$arrStyleSDK = [
    'hengfu' => [
        'default' => [],
    ],
    'chaping' => [
        'default' => [],
    ],
    'kaiping' => [
        'default' => [],
    ],
    'shipintiepian' => [
        'default' => [],
    ],
    'jianglishipin' => [
        'default' => [],
    ],
    'yuansheng' => [
        '上图下文(图片尺寸1280×720)' => [],
        '左图右文(图片尺寸1200×800)' => [],
        '双图双文(大图尺寸1280×720)' => [],
        '纯图片(图片尺寸1280×720)' => [],
    ],
];
$arrStyleAPI = [
    'hudong' => [
        'default' => [],
    ], 
    'tongyong' => [
        'default' => [],
    ],
];
$arrStyleJS = [
    'hengfu' => [
        '640*150' => [],
        '640*280' => [],
    ],
    'chaping' => [
        '510*510' => [],
    ],
    'kaiping' => [
        '750*1344' => [],
    ],
    'xinxiliu' => [
        '225*140' => [],
        '700*280' => [],
    ],
    'fubiao' => [
        '150*150' => [],
    ],
    'yingyongqiang' => [
        '150*150' => [],
    ],
];

$arr = [];
foreach ($arrUpstream as $upstream) {
    foreach ($arrType as $type) {
        foreach (${'arrStyle' . $type} as $style => $arrSize) {
            if ($style == 'yuansheng') {
            }
            foreach ($arrSize as $size => $val) {
                for ($i=0; $i<5; $i++) {
                    $val[$upstream . mt_rand(1000,9000)] = 0;  
                } 
                $arr[$upstream][$type][$style][$size] = $val;
            }
        }
    }
}
$json = json_encode($arr, JSON_UNESCAPED_UNICODE);
//file_put_contents('preslotid.json', $json);

$sql = 'INSERT INTO pre_adslot(app_id,data) VALUES(\'' . $app_id . "','" . $json . "')";

$host = '10.99.202.57';
$port = 3306;
$user = 'hao123';
$pass = 'hao123';
$db = 'szsdb';
$mysqli = new mysqli($host,$user,$pass,$db, $port);
$mysqli->query('truncate table pre_adslot');
$result = $mysqli->query($sql);
var_dump($result);
