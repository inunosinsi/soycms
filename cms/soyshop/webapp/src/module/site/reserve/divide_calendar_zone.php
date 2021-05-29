<?php
function soyshop_divide_calendar_zone($html, $page){

	$obj = $page->create("soyshop_divide_calendar_zone", "HTMLTemplatePage", array(
		"arguments" => array("soyshop_divide_calendar_zone", $html)
	));

	preg_match('/\/\d*_\d{10}/', $_SERVER["REQUEST_URI"], $tmp);
	if(!count($tmp)) header("Location:/" . SOYSHOP_ID);

	$arr = explode("_", ltrim($tmp[0], "/"));
	$itemId = (int)$arr[0];
	$scheduleDate = (int)$arr[1];

	$days = 0;

	// $year = (isset($_GET["y"]) && is_numeric($_GET["y"])) ? (int)$_GET["y"] : (int)date("Y");
	// $month = (isset($_GET["m"]) && is_numeric($_GET["m"])) ? (int)$_GET["m"] : (int)date("n");
	//
	$GLOBALS["reserved_schedules"] = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Reserve.ReserveLogic")->getReservedCountListFromDaysByItemId($itemId, $scheduleDate, $days);
	//
	// //最新の商品を一つ取得する
	// $item = SOY2Logic::createInstance("logic.shop.item.ItemLogic")->getLatestRegisteredItem();
	//
	$schLogic = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Schedule.ScheduleLogic");
	$schList = $schLogic->getScheduleListFromDays($itemId, $scheduleDate, $days);
	// ksort($schList);
	//
	$obj->createAdd("zone_list", "DivideCalendarZoneListComponent", array(
		"soy2prefix" => "block",
		"list" => (count($schList)) ? array_shift($schList) : array(),
		"itemId" => $itemId,
		"labels" => SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Calendar.LabelLogic")->getLabelList($itemId)
	));

	if(is_numeric($itemId)){
		$obj->display();
	}else{
		ob_start();
		$obj->display();
		ob_end_clean();
	}
}

if(!class_exists("DivideCalendarZoneListComponent")){
class DivideCalendarZoneListComponent extends HTMLList{

	private $itemId;
	private $labels;
	private $symbols = array("-", "○");

	protected function populateItem($entity, $schId){
		$symbol = (is_numeric($schId) && is_array($entity) && count($entity)) ? self::_getSymbol($schId, $entity) : 0;

		$labelId = (isset($entity["label_id"]) && is_numeric($entity["label_id"])) ? (int)$entity["label_id"] : 0;
		$this->addLink("sch_link", array(
			"link" => ($symbol > 0) ? soyshop_get_cart_url(true) . "?a=add&schId=" . $schId : null,
			"text" => (isset($this->labels[$labelId])) ? $this->labels[$labelId] : ""
		));

		// $unsoldSeat = (isset($entity["seat"])) ? (int)$entity["seat"] : 0;
		// $schId = (isset($entity["schedule_id"])) ? (int)$entity["schedule_id"] : 0;


		// ○-の条件を調べる
		$this->addLabel("symbol", array(
			"text" => $this->symbols[$symbol]
		));

		// class="nextstep"の条件を調べる
		$this->addModel("class_property", array(
			"attr:class" => ($symbol === 0) ? "nextstep" : ""
		));
	}

	//空いていれば○(1)、空いていなければ-(0)
	private function _getSymbol($schId, $sch){
		$reserves = (isset($GLOBALS["reserved_schedules"]) && is_array($GLOBALS["reserved_schedules"])) ? $GLOBALS["reserved_schedules"] : array();
		if(!count($reserves) || !isset($reserves[$schId])) return 1;	//予約がなければ必ず○
		return ((int)$sch["seat"] - (int)$reserves[$schId]) ? 1 : 0;
	}

	function setItemId($itemId){
		$this->itemId = $itemId;
	}
	function setLabels($labels){
		$this->labels = $labels;
	}
}
}
