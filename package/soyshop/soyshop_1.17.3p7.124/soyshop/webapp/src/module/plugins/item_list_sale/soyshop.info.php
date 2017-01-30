<?php
/*
 */
class SOYShopItemListSaleInfo extends SOYShopInfoPageBase{

	function getPage($active = false){

		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=item_list_sale") . '">セール中商品表示モジュールの設定方法</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info", "item_list_sale", "SOYShopItemListSaleInfo");
?>