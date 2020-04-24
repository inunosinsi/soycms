<?php
class CalendarExpandSeatItemOption extends SOYShopItemOptionBase{

	function clear($index, CartLogic $cart){}
	function compare($postedOption, CartLogic $cart){}
	function doPost($index, CartLogic $cart){}

	/**
	 * 商品情報の下に表示される情報
	 * @param htmlObj, integer index
	 * @return string html
	 */
	function onOutput($htmlObj, $index){
		$cart = CartLogic::getCart();
		$items = $cart->getItems();
		if(!count($items)) return "";

		SOY2::import("module.plugins.reserve_calendar.util.ReserveCalendarUtil");
		SOY2::import("module.plugins.calendar_expand_seat.util.ExpandSeatUtil");

		$html = array();
		foreach($items as $idx => $itemOrder){
			$adultSeat = $cart->getAttribute(ReserveCalendarUtil::getCartAttributeId("seat_div_adult", $idx, $itemOrder->getItemId()));
			$childSeat = $cart->getAttribute(ReserveCalendarUtil::getCartAttributeId("seat_div_child", $idx, $itemOrder->getItemId()));
			$schId = $cart->getAttribute(ReserveCalendarUtil::getCartAttributeId("schedule_id", $idx, $itemOrder->getItemId()));
			$html[] = ExpandSeatUtil::buildBreakdown($schId, $adultSeat, $childSeat);
		}

		return implode("<br>", $html);
	}

	/**
	 * 注文確定時に商品とオプション内容を紐づける
	 * @param integer index
	 */
	function order($index){}

	/**
	 * 注文確定後の注文詳細の商品情報の下に表示される
	 * @param object SOYShop_ItemOrder
	 * @return string html
	 */
	function display(SOYShop_ItemOrder $itemOrder){
		$attrs = $itemOrder->getAttributeList();
		if(!isset($attrs["reserve_id"])) return null;

		SOY2::import("module.plugins.calendar_expand_seat.util.ExpandSeatUtil");
		$scheduleId = ExpandSeatUtil::getScheduleIdByReserveId($attrs["reserve_id"]);

		$html = array();

		//数字を抽出する
		list($adultSeat, $childSeat) = ExpandSeatUtil::extractSeatCompositionByOrderId($itemOrder->getOrderId());
		$html[] = ExpandSeatUtil::buildBreakdown($scheduleId, $adultSeat, $childSeat);

		return implode("<br />", $html);
	}

	function form(SOYShop_ItemOrder $itemOrder){}
	function change($itemOrders){}
	function history($newItemOrder, $oldItemOrder){}
	function add(){}
	function edit($key){}
	function build($itemOrderId, $key, $selected){}
	function buildOnAdmin($index, $fieldValue, $key, $selected){}
}

SOYShopPlugin::extension("soyshop.item.option", "calendar_expand_seat", "CalendarExpandSeatItemOption");
