<?php
/*
 */
class CollectiveItemStockInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){
		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=collective_item_stock") . '">在庫数一括設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "collective_item_stock", "CollectiveItemStockInfo");
