<?php
/*
 */
class B2OrderCSVInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=b2_order_csv").'">B2の設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info","b2_order_csv","B2OrderCSVInfo");
