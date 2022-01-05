<?php
/*
 */
class FacebookLoginInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=facebook_login").'">Facebookログインの設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "facebook_login", "FacebookLoginInfo");
