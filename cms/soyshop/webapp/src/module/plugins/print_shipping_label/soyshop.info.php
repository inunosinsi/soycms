<?php
/*
 */
class PrintShippingLabelInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=print_shipping_label").'">配送伝票印刷プラグインの設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "print_shipping_label", "PrintShippingLabelInfo");
?>