<?php
/*
 */
class GoogleSignInInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=google_sign_in").'">Sign In With Googleの設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "google_sign_in", "GoogleSignInInfo");
