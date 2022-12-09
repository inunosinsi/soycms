<?php
/*
 */
class AttributeOrderCountInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=attribute_order_count").'">購入回数属性自動振り分け</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info", "attribute_order_count", "AttributeOrderCountInfo");
?>
