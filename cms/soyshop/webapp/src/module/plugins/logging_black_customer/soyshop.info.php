<?php
/*
 */
class LoggingBlackCustomerInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=logging_black_customer").'">ブラック顧客リストプラグインの設定の設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "logging_black_customer", "LoggingBlackCustomerInfo");
?>
