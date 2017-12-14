<?php
// nginx.conf client_max_body_size 20M
// php.ini upload_max_filesize=20M
$config['img'] = [
    'upload_path' => 'upload/imgs/',
    'allowed_types' => 'gif|jpg|png|jpeg',
    'max_size' => 102400, // kb
    'overwrite' => true,
];
$config['csv'] = [
    'upload_path' => '/upload/csv/',
    'allowed_types' => 'csv',
    'max_size' => 0, // 无限制
    'overwrite' => true,
];
$config['app'] = [
    'upload_path' => 'upload/apps/',
    'allowed_types' => 'apk|ipa',
    'max_size' => 0, 
    'overwrite' => true,
];
$config['txt'] = [
    'upload_path' => 'upload/txt/',
    'allowed_types' => 'txt',
    'max_size' => 1, // kb 
    'overwrite' => true,
];
