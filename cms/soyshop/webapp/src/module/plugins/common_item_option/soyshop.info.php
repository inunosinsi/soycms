<?php
/*
 */
class CommonItemOptionInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){
		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=common_item_option") . '">商品オプションプラグインの設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "common_item_option", "CommonItemOptionInfo");
