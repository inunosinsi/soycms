<?php
/*
 */
class ShoppingMallInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=shopping_mall").'">簡易ショッピングモール運営プラグインの設定</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info", "shopping_mall", "ShoppingMallInfo");