<?php
/*
 */
class ItemStandardInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){

		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=item_standard") . '">商品規格プラグインの設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "item_standard", "ItemStandardInfo");
