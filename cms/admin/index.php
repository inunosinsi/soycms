<?php
include("../common/common.inc.php");
include('./webapp/config.inc.php');
//extモードのファイルを読み込む
if(file_exists(dirname(__FILE__) . "/webapp/config.ext.php")) include_once('./webapp/config.ext.php');

// IPアドレスによるアクセス制限
include_once(dirname(__FILE__) . "/restrict.php");

try{
	SOY2PageController::run();
}catch(Exception $e){
	$exception = $e;
	include_once(SOY2::RootDir() . "error/admin.php");
}
