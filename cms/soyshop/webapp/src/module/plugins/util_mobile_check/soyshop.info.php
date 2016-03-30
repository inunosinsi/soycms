<?php
/*
 */
class UtilMobileCheckInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=util_mobile_check").'">携帯転送の設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "util_mobile_check", "UtilMobileCheckInfo");
