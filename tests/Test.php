<?php
require '../vendor/autoload.php';

use \webin;

$mysql = new mysqli(
    '127.0.0.1', //hostname 主机
    'root', //username 用户名
    '123456', //password 密码
    'test', //database 库
    '3306' //port 端口
);
$tablePre = '';
$tableName = 'user';
$savePath = "/Users/webin/Project/build-table-model/tests/";
//完整示例
$Tii = new webin\BuildModel($mysql, $tableName, 'app\\common\\model', $savePath, '', "\app\common\model\BaseModel");
$Tii->create();
