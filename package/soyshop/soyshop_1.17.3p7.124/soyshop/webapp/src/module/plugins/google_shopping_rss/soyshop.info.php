<?php
/*
 */
class GoogleShoppingRssInfo extends SOYShopInfoPageBase{

	function getPage($active = false){

		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=google_shopping_rss") . '">全商品表示モジュールの設定方法</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info", "google_shopping_rss", "GoogleShoppingRssInfo");
?>