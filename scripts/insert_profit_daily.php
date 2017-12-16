<?php
// tab_media_user_profit_sum_daily 假数据
// app_id : 0 - 1000
// account_id : 0 - 800
// post_profit : 
// select app_id,acct_id,sum(post_profit) from tab_media_user_profit_sum_daily group by app_id;
// select app_id,acct_id,sum(post_profit) from tab_media_user_profit_sum_daily where create_time>2000 and create_time<10000 group by app_id;
// INSERT INTO monthly_bill(app_id,account_id,money) (SELECT app_id,acct_id,SUM(post_profit) FROM tab_media_user_profit_sum_daily WHERE create_time>2000 AND create_time<10000 GROUP BY app_id)

$sql = 'insert into tab_media_user_profit_sum_daily(app_id,account_id,media_name,post_profit,create_time) values';
    
$intTime = 1512911011;
for ($app_id = 0; $app_id < 3000; $app_id++) {
    for ($dt = 1; $dt < 31; $dt++) {
        for ($upstream = 0; $upstream < 3; $upstream++) {
            $sql .= "('" . substr(md5($app_id),0,8) . "','"
                . md5(mt_rand(0,800)) . "','"
                . '宋梓槊test' . "',"
                . mt_rand(0,99999) . '.' . mt_rand(1,99) . ","
                . ($intTime + $upstream) . "),";
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
echo $mysqli->error;
var_dump($result);
