<?php
/*
 */
class PaymentConstructionInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){

		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=payment_construction") . '">施工用手数料計算の設定</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info", "payment_construction", "PaymentConstructionInfo");
