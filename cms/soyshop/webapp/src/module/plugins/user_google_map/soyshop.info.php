<?php
/*
 */
class UserGoogleMapInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=user_google_map") . '">顧客住所GoogleMaps連携プラグイン</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "user_google_map", "UserGoogleMapInfo");
