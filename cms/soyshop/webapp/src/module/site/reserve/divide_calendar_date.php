<?php
function soyshop_divide_calendar_date($html, $page){

	$obj = $page->create("soyshop_divide_calendar_date", "HTMLTemplatePage", array(
		"arguments" => array("soyshop_divide_calendar_date", $html)
	));

	$year = (isset($_GET["y"]) && is_numeric($_GET["y"])) ? (int)$_GET["y"] : (int)date("Y");
	$month = (isset($_GET["m"]) && is_numeric($_GET["m"])) ? (int)$_GET["m"] : (int)date("n");

	$GLOBALS["reserved_schedules"] = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Reserve.ReserveLogic")->getReservedSchedulesByPeriod($year, $month);

	//最新の商品を一つ取得する
	$item = SOY2Logic::createInstance("logic.shop.item.ItemLogic")->getLatestRegisteredItem();

	$schLogic = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Schedule.ScheduleLogic");
	$schList = $schLogic->getScheduleListFromDays($item->getId(), time(), 30);
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

	protected function populateItem($entity, $time){
		$this->addLink("day", array(
			"link" => "/" . SOYSHOP_ID . "/zone/" . $this->itemId . "_" . $time,	//商品ID _ スケジュールのタイムスタンプを渡す
			"text" => (is_numeric($time)) ? self::_convert($time) : ""
		));
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
