<?php

class CalendarExpandSeatOrderMail extends SOYShopOrderMail{

	/**
	 * メール本文を取得
	 * @return string
	 */
	function getMailBody(SOYShop_Order $order){

		$bodies = array();

		//内訳
		$attrs = $order->getAttributeList();
		SOY2::import("module.plugins.reserve_calendar.domain.SOYShopReserveCalendar_ReserveDAO");
		try{
			$resId = SOY2DAOFactory::create("SOYShopReserveCalendar_ReserveDAO")->getByOrderId($order->getId())->getId();
		}catch(Exception $e){
			$resId = null;
		}
		if(isset($resId)){
			SOY2::import("module.plugins.calendar_expand_seat.util.ExpandSeatUtil");
			$scheduleId = ExpandSeatUtil::getScheduleIdByReserveId($resId);
			list($adultSeat, $childSeat) = ExpandSeatUtil::extractSeatCompositionByOrderId($order->getId());
			$str = ExpandSeatUtil::buildBreakdown($scheduleId, $adultSeat, $childSeat);
			$str = str_replace("<br>", "\n", $str);
			$str = str_replace("&nbsp;", " ", $str);
			$bodies[] = $str;
		}

		foreach($order->getAttributeList() as $attrId => $attr){
			if(strpos($attrId, "emergency_") !== false || strpos($attrId, "relationship") !== false){
				$bodies[] = "\n" . $attr["name"] . "\n" . $attr["value"];
			} else if(strpos($attrId, "representative") !== false || strpos($attrId, "companion") !== false){
				if(strpos($attrId, "companion") !== false){	//同行者の場合
					preg_match('/companion(.*)/', $attrId, $tmp);
					if(!isset($tmp[1]) || !is_numeric($tmp[1])) continue;
				}
				$bodies[] = "\n" . $attr["name"] . "\n" . $attr["value"];
			}
		}

		$bodies[] = "";	//改行

		return implode("\n", $bodies);
	}


	function getDisplayOrder(){
		return 2; //簡易予約カレンダーの次
	}
}

SOYShopPlugin::extension("soyshop.order.mail.user", "calendar_expand_seat", "CalendarExpandSeatOrderMail");
SOYShopPlugin::extension("soyshop.order.mail.admin", "calendar_expand_seat", "CalendarExpandSeatOrderMail");
