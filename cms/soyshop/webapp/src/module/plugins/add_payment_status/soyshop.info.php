<?php
/*
 */
class AddPaymentStatusInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=add_payment_status").'">支払い状況項目追加プラグイン</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info", "add_payment_status", "AddPaymentStatusInfo");
