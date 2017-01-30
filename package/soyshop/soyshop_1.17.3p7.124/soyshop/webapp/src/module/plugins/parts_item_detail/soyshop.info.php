<?php
/*
 */
class ItemDetailModuleInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=parts_item_detail") . '">商品詳細表示プラグインの設定方法</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info", "parts_item_detail", "ItemDetailModuleInfo");
?>