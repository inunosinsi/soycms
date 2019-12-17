<?php
/*
 */
class GeneratePasswordInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=generate_password") . '">マイページログイン用パスワード自動生成プラグイン</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "generate_password", "GeneratePasswordInfo");
