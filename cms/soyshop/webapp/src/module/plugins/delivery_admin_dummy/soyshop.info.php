<?php
/*
 */
class DeliveryAdminDummyInfo extends SOYShopInfoPageBase{

	function getPage($active = false){

		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=delivery_admin_dummy") . '">配送ダミーモジュールの設定</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info", "delivery_admin_dummy", "DeliveryAdminDummyInfo");
