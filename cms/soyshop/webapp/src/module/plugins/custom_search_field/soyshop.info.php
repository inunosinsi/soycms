<?php
/*
 */
class CustomSearchFieldInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){
		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=custom_search_field") . '">カスタムサーチフィールド</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "custom_search_field", "CustomSearchFieldInfo");
