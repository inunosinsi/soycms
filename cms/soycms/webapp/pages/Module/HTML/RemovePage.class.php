<?php

class RemovePage extends CMSWebPageBase{

    function __construct($args) {

    	$moduleId = $_GET["moduleId"];
    	$modulePath = UserInfoUtil::getSiteDirectory() . ".module/html/" . str_replace(".", "/", $moduleId) . ".php";
    	$moduleIniPath = UserInfoUtil::getSiteDirectory() . ".module/html/" . str_replace(".", "/", $moduleId) . ".ini";

    	//モジュールのPHPファイルとiniファイルを削除
    	try{
    		unlink($modulePath);
    		unlink($moduleIniPath);
    	}catch(Exception $e){
    		//
    	}

    	$this->jump("Module");
    }
}
