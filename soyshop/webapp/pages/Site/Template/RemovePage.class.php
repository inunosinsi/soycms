<?php

class RemovePage extends WebPage{

    function __construct($args) {

		$templateType = $_GET["type"];
		$templateId = $_GET["id"];

		$templatePath = SOYSHOP_SITE_DIRECTORY . ".template/" . $templateType."/";

		//テンプレートのHTMLファイルとiniファイルを削除
		try{
			unlink($templatePath.$templateId.".html");
			unlink($templatePath.$templateId.".ini");
		}catch(Exception $e){

		}

		SOYShopCacheUtil::clearCache();

    	SOY2PageController::jump("Site.Template");

    	exit;
    }
}
