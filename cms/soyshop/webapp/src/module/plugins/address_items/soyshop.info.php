<?php
/*
 */
class AddressItemsInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=address_items").'">住所項目の設定</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info", "address_items", "AddressItemsInfo");
