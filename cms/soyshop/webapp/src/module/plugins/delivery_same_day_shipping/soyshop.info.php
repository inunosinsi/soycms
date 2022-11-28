<?php
/*
 */
class DeliverySameDayShippingInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=delivery_same_day_shipping").'">即日配送表示の設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "delivery_same_day_shipping", "DeliverySameDayShippingInfo");
?>