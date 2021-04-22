<?php
/*
 */
class TakeOverCustomerInfoInfo extends SOYShopInfoPageBase{

	function getPage($active = true){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=take_over_customer_info").'">別サイト顧客情報引継ぎプラグインの設定</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info", "take_over_customer_info", "TakeOverCustomerInfoInfo");
