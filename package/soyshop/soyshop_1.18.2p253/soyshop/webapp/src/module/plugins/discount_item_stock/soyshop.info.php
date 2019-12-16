<?php
/*
 */
class DiscountItemStockInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=discount_item_stock") . '">在庫数値引き設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "discount_item_stock", "DiscountItemStockInfo");
?>