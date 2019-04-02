<?php

class ReserveCalendarOrderMail extends SOYShopOrderMail{

	/**
	 * メール本文を取得
	 * @return string
	 */
	function getMailBody(SOYShop_Order $order){

		//仮登録モードの場合、注文内容毎にトークンを発行
		SOY2::import("module.plugins.reserve_calendar.util.ReserveCalendarUtil");
		$config = ReserveCalendarUtil::getConfig();
		if(isset($config["tmp"]) && $config["tmp"] == ReserveCalendarUtil::IS_TMP && $order->getStatus() == SOYShop_Order::ORDER_STATUS_INTERIM){	//仮登録であるか？も調べておく
			$tokens = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Reserve.ReserveLogic")->getTokensByOrderId($order->getId());
			//一つでもクリックすればすべての予約が終わるようにする
			if(isset($tokens[0])){
				$url = soyshop_get_cart_url(false, true) . "?soyshop_notification=reserve_calendar&token=" . $tokens[0];
				return "予約を完了するには下記のリンクをクリックします。\n" . $url . "\n";	//@ToDo文章
			}
		}
	}


	function getDisplayOrder(){
		return 1; //大事なので1
	}
}

SOYShopPlugin::extension("soyshop.order.mail.user", "reserve_calendar", "ReserveCalendarOrderMail");
SOYShopPlugin::extension("soyshop.order.mail.admin", "reserve_calendar", "ReserveCalendarOrderMail");
