<?php
/*
 */
class CustomModuleInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=payment_custom").'">カスタム支払モジュールの設定画面へ</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info","payment_custom","CustomModuleInfo");
