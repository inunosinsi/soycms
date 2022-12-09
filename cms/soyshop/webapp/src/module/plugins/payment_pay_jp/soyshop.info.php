<?php
/*
 */
class PayJpInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=payment_pay_jp").'">PAY.JPクレジットカード支払いの設定画面へ</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info", "payment_pay_jp", "PayJpInfo");
