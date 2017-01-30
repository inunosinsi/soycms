<?php
/*
 */
class SimpleNewsModuleInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=common_simple_news").'">新着情報の設定</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info", "common_simple_news", "SimpleNewsModuleInfo");
?>