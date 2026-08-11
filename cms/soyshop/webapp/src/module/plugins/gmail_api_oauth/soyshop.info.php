<?php
/*
 */
class GmailAPIOAuthInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){
		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=gmail_api_oauth") . '">Gmail API(OAuth2.0認証)プラグインの設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "gmail_api_oauth", "GmailAPIOAuthInfo");
