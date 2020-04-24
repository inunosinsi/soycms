<?php

class CalendarExpandSeatCart extends SOYShopCartBase{

	function __construct(){
		SOY2::import("module.plugins.reserve_calendar.util.ReserveCalendarUtil");
	}

	function afterOperation(CartLogic $cart){
		$items = $cart->getItems();
		if(count($items)){
			$schLogic = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Schedule.ScheduleLogic");
			SOY2::import("module.plugins.reserve_calendar.domain.SOYShopReserveCalendar_SchedulePriceDAO");
			foreach($items as $idx => $itemOrder){
				$adultSeat = $cart->getAttribute(ReserveCalendarUtil::getCartAttributeId("seat_div_adult", $idx, $itemOrder->getItemId()));
				$childSeat = $cart->getAttribute(ReserveCalendarUtil::getCartAttributeId("seat_div_child", $idx, $itemOrder->getItemId()));

				$schId = $cart->getAttribute(ReserveCalendarUtil::getCartAttributeId("schedule_id", $idx, $itemOrder->getItemId()));
				$schPrice = (int)$schLogic->getScheduleById($schId)->getPrice();

				$total = $adultSeat * $schPrice;

				//子供料金
				try{
					$childPrice = SOY2DAOFactory::create("SOYShopReserveCalendar_SchedulePriceDAO")->get($schId, "child_price")->getPrice();
				}catch(Exception $e){
					$childPrice = null;
				}
				
				if(!is_null($childPrice) && is_numeric($childPrice) && $childPrice >= 0){
					$total += $childSeat * $childPrice;
				}else{
					$total += $childSeat * $schPrice;
				}

				$itemOrder->setTotalPrice($total);	//商品毎の合計のみ更新してみる
				$itemOrder->setItemCount($adultSeat + $childSeat);
				$items[$idx] = $itemOrder;
			}
			$cart->setItems($items);
			$cart->save();
		}
	}

	function isUpdate(CartLogic $cart){
		return false;
	}

	function displayPage01(CartLogic $cart){
		$items = $cart->getItems();
		if(count($items)){
			$js = array();
			$js[] = self::buildHiddenSeatForms($cart, $items);
			$js[] = "<script>";
			$js[] = file_get_contents(dirname(__FILE__) . "/js/cart1.js");
			$js[] = "</script>";
			return implode("\n", $js);
		}
	}

	function displayPage02(CartLogic $cart){
		return self::_cancelValidate();
	}

	function displayPage03(CartLogic $cart){
		return self::_cancelValidate();
	}

	private function _cancelValidate(){
		$js = array();
		$js[] = "<script>";
		$js[] = file_get_contents(dirname(__FILE__) . "/js/cancel.js");
		$js[] = "</script>";
		return implode("\n", $js);
	}

	function displayPage04(CartLogic $cart){
		$items = $cart->getItems();
		if(count($items)){
			$js = array();
			$js[] = self::buildHiddenSeatForms($cart, $items);
			$js[] = "<script>";
			$js[] = file_get_contents(dirname(__FILE__) . "/js/cart4.js");
			$js[] = "</script>";
			return implode("\n", $js);
		}
	}

	private function buildHiddenSeatForms(CartLogic $cart, $items){
		$idx = key($items);
		$adult = $cart->getAttribute(ReserveCalendarUtil::getCartAttributeId("seat_div_adult", $idx, $items[$idx]->getItemId()));
		$child = $cart->getAttribute(ReserveCalendarUtil::getCartAttributeId("seat_div_child", $idx, $items[$idx]->getItemId()));

		$html = array();
		$html[] = "<input type=\"hidden\" id=\"adult\" value=\"" . $adult . "\">";
		$html[] = "<input type=\"hidden\" id=\"child\" value=\"" . $child . "\">";

		return implode("\n", $html);
	}
}
SOYShopPlugin::extension("soyshop.cart", "calendar_expand_seat", "CalendarExpandSeatCart");
