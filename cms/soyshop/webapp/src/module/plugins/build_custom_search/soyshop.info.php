<?php
/*
 */
class BuildCustomSearchInfo extends SOYShopInfoPageBase{

	function getPage($active = true){
		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=build_custom_search") . '">詳細検索の設定</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info", "build_custom_search", "BuildCustomSearchInfo");
