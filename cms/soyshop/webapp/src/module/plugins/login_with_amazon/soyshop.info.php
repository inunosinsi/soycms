<?php
/*
 */
class LoginWithAmazonInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=login_with_amazon").'">Login With Amazonの設定</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info", "login_with_amazon", "LoginWithAmazonInfo");
