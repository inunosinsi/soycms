<?php
/*
 */
class ItemOrderPriceBulkChangeInfo extends SOYShopInfoPageBase{

	function getPage($active = false){

		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=itemorder_price_bulk_change") . '">注文商品の単価一括変更プラグインの設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "itemorder_price_bulk_change", "ItemOrderPriceBulkChangeInfo");
