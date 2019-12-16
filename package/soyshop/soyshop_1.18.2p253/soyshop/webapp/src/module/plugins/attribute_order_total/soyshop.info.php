<?php
/*
 */
class AttributeOrderTotalInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=attribute_order_total").'">購入金額属性自動振り分け</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "attribute_order_total", "AttributeOrderTotalInfo");
?>
