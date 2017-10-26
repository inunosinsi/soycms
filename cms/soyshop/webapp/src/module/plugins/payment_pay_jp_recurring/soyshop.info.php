<?php
/*
 */
class PayJpRecurringInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=payment_pay_jp_recurring").'">PAY.JP定期課金の設定画面へ</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info", "payment_pay_jp_recurring", "PayJpRecurringInfo");
