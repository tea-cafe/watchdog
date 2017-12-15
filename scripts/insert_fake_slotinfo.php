<?php
$sql = 'insert into adslot_info values';
    
for ($i = 0; $i<37; $i++) {
    $sql .= "(0,1,'$i','fr$i','gkhkj$i',9,'asdfa','asdfa','34f','4asf','asdf',0,'asdf','asdfas',0,'asda',0,0),";
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

