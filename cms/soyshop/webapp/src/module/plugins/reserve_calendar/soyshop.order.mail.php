<?php

class ReserveCalendarOrderMail extends SOYShopOrderMail{

	/**
	 * メール本文を取得
	 * @return string
	 */
	function getMailBody(SOYShop_Order $order){

		//仮登録モードの場合、注文内容毎にトークンを発行 @ToDo まとめて注文の場合を検討
		SOY2::import("module.plugins.reserve_calendar.util.ReserveCalendarUtil");
		$config = ReserveCalendarUtil::getConfig();
		if(isset($config["tmp"]) && $config["tmp"] == ReserveCalendarUtil::IS_TMP){

		}
	}


	function getDisplayOrder(){
		return 1; //大事なので1
	}
}

SOYShopPlugin::extension("soyshop.order.mail.user", "reserve_calendar", "ReserveCalendarOrderMail");
SOYShopPlugin::extension("soyshop.order.mail.admin", "reserve_calendar", "ReserveCalendarOrderMail");
