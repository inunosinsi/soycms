<?php
/*
 */
class DeliveryCountFreeModuleInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=delivery_count_free").'">配送料の設定</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info","delivery_count_free","DeliveryCountFreeModuleInfo");
?>
