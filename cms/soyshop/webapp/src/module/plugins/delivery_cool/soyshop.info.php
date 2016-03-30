<?php
/*
 */
class DeliveryCoolModuleInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=delivery_cool") . '">配送料、配達時間帯の設定</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info", "delivery_cool", "DeliveryCoolModuleInfo");
?>
