<?php
function soyshop_divide_calendar_date($html, $page){

	$obj = $page->create("soyshop_divide_calendar_date", "HTMLTemplatePage", array(
		"arguments" => array("soyshop_divide_calendar_date", $html)
	));

	$year = (isset($_GET["y"]) && is_numeric($_GET["y"])) ? (int)$_GET["y"] : (int)date("Y");
	$month = (isset($_GET["m"]) && is_numeric($_GET["m"])) ? (int)$_GET["m"] : (int)date("n");


	//最新の商品を一つ取得する
	$item = SOY2Logic::createInstance("logic.shop.item.ItemLogic")->getLatestRegisteredItem();

	$days = 30;

	$GLOBALS["reserved_schedules"] = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Reserve.ReserveLogic")->getReservedCountListFromDaysByItemId($item->getId(), time(), $days);

	$schLogic = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Schedule.ScheduleLogic");
	$schList = $schLogic->getScheduleListFromDays($item->getId(), time(), $days);
	ksort($schList);

	$obj->createAdd("day_list", "DivideCalendarDateListComponent", array(
		"soy2prefix" => "block",
		"list" => $schList,
		"itemId" => $item->getId()
	));

	if(is_numeric($item->getId())){
		$obj->display();
	}else{
		ob_start();
		$obj->display();
		ob_end_clean();
	}
}

if(!class_exists("DivideCalendarDateListComponent")){
class DivideCalendarDateListComponent extends HTMLList{

	private $itemId;
	private $days = array("日", "月", "火", "水", "木", "金", "土");
	private $symbols = array("-", "△", "○");

	protected function populateItem($entity, $time){
		$symbol = (is_array($entity) && count($entity)) ? self::_getSymbol($entity) : 0;

		$this->addLink("day", array(
			"link" => ($symbol > 0) ? "/" . SOYSHOP_ID . "/zone/" . $this->itemId . "_" . $time : null,	//商品ID _ スケジュールのタイムスタンプを渡す
			"text" => (is_numeric($time)) ? self::_convert($time) : ""
		));

		//$unsoldSeat = (is_array($entity)) ? self::_culcUnsoldSeat($entity) : 0;
		//$schIds = (is_array($entity)) ? self::_getScheduleIds($entity) : array();

		// ○△-の条件を調べる	仮　すべての日が空いていれば○(2)、一日でも空いていなければ△(1)、全部の日が空いていなければ-(0)
		$this->addLabel("symbol", array(
			"text" => $this->symbols[$symbol]
		));

		// class="no-style"の条件を調べる
		$this->addModel("class_property", array(
			"attr:class" => ($symbol === 0) ? "no-style" : ""
		));
	}

	private function _culcUnsoldSeat($schs){
		$total = 0;
		foreach($schs as $sch){
			if(!isset($sch["seat"]) || !is_numeric($sch["seat"])) continue;
			$total += (int)$sch["seat"];
		}

		return $total;
	}

	private function _getScheduleIds($schs){
		$list = array();
		foreach($schs as $schId => $sch){
			$list[] = $schId;
		}
		return $list;
	}

	//すべての日が空いていれば○(2)、一日でも空いていなければ△(1)、全部の日が空いていなければ-(0)
	private function _getSymbol($schs){
		$reserves = (isset($GLOBALS["reserved_schedules"]) && is_array($GLOBALS["reserved_schedules"])) ? $GLOBALS["reserved_schedules"] : array();
		if(!count($reserves)) return 2;	//予約がなければ必ず○

		$unsoldSeatList = array();
		foreach($schs as $schId => $sch){
			$unsoldSeatList[$schId] = $sch["seat"];
			if(isset($reserves[$schId])) $unsoldSeatList[$schId] -= (int)$reserves[$schId];
		}

		$zero = 0;	//ゼロの予定が何個あるか？
		foreach($unsoldSeatList as $unsold){
			if($unsold === 0) $zero++;
		}

		if($zero === 0) return 2;
		return ($zero === count($unsoldSeatList)) ? 0 : 1;
	}

	private function _convert($time){
		$str = date("n月j日", $time);
		$str .= "（" . $this->days[date("w", $time)] . "）";
		return $str;
	}

	function setItemId($itemId){
		$this->itemId = $itemId;
	}
}
}
