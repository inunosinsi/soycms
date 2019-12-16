<?php
/*
 */
class GoogleSignInInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=google_sign_in").'">Google Sign-In for Websitesの設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "google_sign_in", "GoogleSignInInfo");
