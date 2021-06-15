<?php

class ReserveCalendarOrderMail extends SOYShopOrderMail{

	/**
	 * メール本文を取得
	 * @return string
	 */
	function getMailBody(SOYShop_Order $order){

		$bodies = array();

		try{
			$itemOrders = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO")->getByOrderId($order->getId());
		}catch(Exception $e){
			$itemOrders = array();
		}

		//プラン詳細
		if(count($itemOrders)){
			SOY2::import("module.plugins.reserve_calendar.domain.SOYShopReserveCalendar_ScheduleDAO");
			$schDao = SOY2DAOFactory::create("SOYShopReserveCalendar_ScheduleDAO");

			$bodies[] = "";
			$bodies[] = "予約詳細";
			$bodies[] = "-----------------------------------------";

			foreach($itemOrders as $itemOrder){
				$attrs = $itemOrder->getAttributeList();
				if(!isset($attrs["reserve_id"])) continue;

				try{
					$sch = $schDao->getScheduleByReserveId($attrs["reserve_id"]);
				}catch(Exception $e){
					continue;
				}

				$labels = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Calendar.LabelLogic")->getLabelList($itemOrder->getItemId());
				if(isset($labels[$sch->getLabelId()])){
					$bodies[] = $itemOrder->getItemName() . " " . $sch->getYear() . "-" . $sch->getMonth() . "-"  . $sch->getDay() . " " . $labels[$sch->getLabelId()];
				}
			}
			$bodies[] = "";
		}

		foreach($order->getAttributeList() as $attrId => $attr){
			if(strpos($attrId, "reserve_manager_composition") === false) continue;
			$bodies[] = "\n" . $attr["name"] . "：" . $attr["value"];
		}

		SOY2::import("module.plugins.reserve_calendar.util.ReserveCalendarUtil");
		$config = ReserveCalendarUtil::getConfig();

		//仮登録モードの場合、注文内容毎にトークンを発行
		if(isset($config["tmp"]) && $config["tmp"] == ReserveCalendarUtil::IS_TMP && $order->getStatus() == SOYShop_Order::ORDER_STATUS_INTERIM){	//仮登録であるか？も調べておく
			//仮登録モードの時にメール本文に本登録用のURLを挿入するか？
			if(isset($config["send_at_time_tmp"]) && $config["send_at_time_tmp"] == ReserveCalendarUtil::IS_SEND){
				$tokens = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Reserve.ReserveLogic")->getTokensByOrderId($order->getId());
				//一つでもクリックすればすべての予約が終わるようにする
				if(isset($tokens[0])){
					$url = soyshop_get_cart_url(false, true) . "?soyshop_notification=reserve_calendar&token=" . $tokens[0];
					$bodies[] = "\n予約を完了するには下記のリンクをクリックします。\n" . $url . "\n";	//@ToDo文章
				}
			}
		}

		return implode("\n", $bodies);
	}


	function getDisplayOrder(){
		return 1; //大事なので1
	}
}

SOYShopPlugin::extension("soyshop.order.mail.user", "reserve_calendar", "ReserveCalendarOrderMail");
SOYShopPlugin::extension("soyshop.order.mail.admin", "reserve_calendar", "ReserveCalendarOrderMail");
SOYShopPlugin::extension("soyshop.order.mail.confirm", "reserve_calendar", "ReserveCalendarOrderMail");
