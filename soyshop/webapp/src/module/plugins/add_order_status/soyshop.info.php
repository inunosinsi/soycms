<?php
/*
 */
class AddOrderStatusInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=add_order_status").'">注文状態項目追加プラグイン</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info", "add_order_status", "AddOrderStatusInfo");
