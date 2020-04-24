<?php
class ItemSubtitleInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=common_item_subtitle") . '">商品名サブタイトルプラグインの使い方</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info", "common_item_subtitle", "ItemSubtitleInfo");
