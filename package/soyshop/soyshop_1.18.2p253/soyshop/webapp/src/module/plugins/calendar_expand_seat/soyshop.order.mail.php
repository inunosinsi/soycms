<?php

class CalendarExpandSeatOrderMail extends SOYShopOrderMail{

	/**
	 * メール本文を取得
	 * @return string
	 */
	function getMailBody(SOYShop_Order $order){
		$bodies = array();

		//内訳
		try{
			$itemOrders = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO")->getByOrderId($order->getId());
		}catch(Exception $e){
			$itemOrders = array();
		}

		if(count($itemOrders)){
			foreach($itemOrders as $itemOrder){
				$attrs = $itemOrder->getAttributeList();
				if(isset($attrs["reserve_id"])){
					SOY2::import("module.plugins.calendar_expand_seat.util.ExpandSeatUtil");
					$scheduleId = ExpandSeatUtil::getScheduleIdByReserveId($attrs["reserve_id"]);
					list($adultSeat, $childSeat) = ExpandSeatUtil::extractSeatCompositionByOrderId($order->getId());
					$str = ExpandSeatUtil::buildBreakdown($scheduleId, $adultSeat, $childSeat);
					$str = str_replace("<br>", "\n", $str);
					$str = str_replace("&nbsp;", " ", $str);
					$bodies[] = $str;
					$bodies[] = "";	//改行
				}
			}
		}

		return implode("\n", $bodies);
	}


	function getDisplayOrder(){
		return 2; //簡易予約カレンダーの次
	}
}

SOYShopPlugin::extension("soyshop.order.mail.user", "calendar_expand_set", "CalendarExpandSeatOrderMail");
SOYShopPlugin::extension("soyshop.order.mail.admin", "calendar_expand_set", "CalendarExpandSeatOrderMail");
