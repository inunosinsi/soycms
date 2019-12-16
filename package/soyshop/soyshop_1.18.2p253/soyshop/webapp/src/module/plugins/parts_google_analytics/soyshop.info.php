<?php
/*
 */
class GoogleAnalyticsPluginInfo extends SOYShopInfoPageBase{

	function getPage($active = false){

		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=parts_google_analytics") . '">トラッキングコードの設定</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info", "parts_google_analytics", "GoogleAnalyticsPluginInfo");
?>