<?php
/*
 */
class BlackCustomerListInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=black_customer_list").'">ブラック顧客リストプラグインの設定の設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "black_customer_list", "BlackCustomerListInfo");
