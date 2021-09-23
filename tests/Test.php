<?php
require '../vendor/autoload.php';
use \webin;
$table = 'user';
if (isset($dir)) {
    $namespace="app\\common\\model\\".$dir;
} else {
    $namespace="app\\common\\model";
}
$mysql = new mysqli(
    '39.108.80.66',
    'root',
    'launch*CS2018',
    'intelligent_marketing',
    '3306'
);
$tablePre = '';
$Tii = new webin\BuildModel($mysql, $tablePre, $table,$namespace);

$search = array(
    '{%fields%}',
    '{%tableName%}',
    '{%trueTableName%}',
    '{%dbName%}',
    '{%className%}',
    '{%_auto%}',
    '{%_validate%}',
    '{%namespace%}',
    '{%property%}');
$replace = array(
    $Tii->getFieldString(),
    $Tii->getTableName(),
    $Tii->getTrueTableName(),
    $Tii->getDbName(),
    $Tii->getModelClassName(),
    $Tii->getAutoFill(),
    $Tii->getAutoValidate(),
    $namespace,
    $Tii->getProperty());
$str = ucwords(str_replace('_', ' ', $Tii->getTableName()));
$str = str_replace(' ','',lcfirst($str));
$modelName = $str ? ucfirst($str) : $str;
$basePath = "./";
if (isset($dir)) {
    $newDir = $basePath . $dir;
    if (!is_dir($newDir)) {
        mkdir($newDir);
    }
    $path =  $newDir. '/' . $modelName. '.php';
} else {
    $path = $basePath . $modelName . '.php';
}
$classString = str_replace($search, $replace, $Tii->getTiiTpl());
file_put_contents($path,$classString);
echo "Model " . $modelName . " created success";exit;
