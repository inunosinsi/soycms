<?php
/*
 */
class FacebookCatalogManagerInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=facebook_catalog_manager").'">Facebookカタログ用XML出力プラグインの使い方</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "facebook_catalog_manager", "FacebookCatalogManagerInfo");
