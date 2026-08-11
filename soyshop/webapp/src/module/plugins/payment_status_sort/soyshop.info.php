<?php
/*
 */
class PaymentStatusSortInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){
		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=payment_status_sort") . '">支払い状況並び順の設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "payment_status_sort", "PaymentStatusSortInfo");
