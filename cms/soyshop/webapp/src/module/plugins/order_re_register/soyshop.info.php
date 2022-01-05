<?php
/*
 */
class OrderReRegisterInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){
		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=order_re_register") . '">注文再登録プラグイン設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "order_re_register", "OrderReRegisterInfo");
