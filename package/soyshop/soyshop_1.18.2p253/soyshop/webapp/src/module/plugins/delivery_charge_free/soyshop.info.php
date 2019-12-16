<?php
/*
 */
class DeliveryChargeFreeModuleInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=delivery_charge_free").'">配送料の設定</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info","delivery_charge_free","DeliveryChargeFreeModuleInfo");
?>
