<?php
/*
 */
class SkipCartPageInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){

		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=skip_cart_page").'">カートページスキッププラグインの設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info","skip_cart_page","SkipCartPageInfo");
