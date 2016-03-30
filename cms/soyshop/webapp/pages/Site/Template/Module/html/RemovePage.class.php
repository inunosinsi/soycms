<?php

class RemovePage extends WebPage{

    function RemovePage($args) {
 
    	$moduleId = $_GET["moduleId"];
    	$modulePath = SOYSHOP_SITE_DIRECTORY . ".module/html/" . str_replace(".", "/", $moduleId) . ".php";
    	$moduleIniPath = SOYSHOP_SITE_DIRECTORY . ".module/html/" . str_replace(".", "/", $moduleId) . ".ini";
    	
    	//モジュールのPHPファイルとiniファイルを削除
    	try{
    		unlink($modulePath);
    		unlink($moduleIniPath);
    	}catch(Exception $e){
    		//
    	}
	
    	SOY2PageController::jump("Site.Template#module_list");
    }
}
?>