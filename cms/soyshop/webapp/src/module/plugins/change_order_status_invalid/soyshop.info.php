<?php

class ChangeOrderStatusInvalidInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=change_order_status_invalid").'">自動注文無効プラグインの設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "change_order_status_invalid", "ChangeOrderStatusInvalidInfo");
