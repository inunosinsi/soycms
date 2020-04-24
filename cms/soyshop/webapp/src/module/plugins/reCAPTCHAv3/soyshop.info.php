<?php
/*
 */
class reCAPTCHAv3Info extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=reCAPTCHAv3").'">Google reCAPTCHA v3の設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "reCAPTCHAv3", "reCAPTCHAv3Info");
