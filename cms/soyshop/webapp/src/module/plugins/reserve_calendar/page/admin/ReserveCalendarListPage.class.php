<?php

class ReserveCalendarListPage extends WebPage{
	
	private $configObj;
	private $itemId;
	
	function __construct(){
		SOY2::import("module.plugins.reserve_calendar.util.ReserveCalendarUtil");
		
		$this->y = (isset($_GET["y"]) && (int)$_GET["y"] > 0) ? (int)$_GET["y"] : date("Y");
		$this->m = (isset($_GET["m"]) && (int)$_GET["m"] > 0) ? (int)$_GET["m"] : date("n");
		$this->itemId = (isset($_GET["item_id"]) && strlen($_GET["item_id"])) ? (int)$_GET["item_id"] : null;
	}
	
	function execute(){
		WebPage::__construct();
		
		//再予約モード
		if(soy2_check_token() && isset($_GET["re_reserve"]) && is_numeric($_GET["re_reserve"])){
			ReserveCalendarUtil::saveSessionValue("user", (int)$_GET["re_reserve"]);
		}
				
		$this->addSelect("item_select", array(
			"name" => "item_id",
			"options" => SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Calendar.LabelLogic")->getRegisteredItemsOnLabel(),
			"selected" => $this->itemId,
			"attr:id" => "item_select",
			"attr:onchange" => "redirectAfterSelectOfSch()"
		));
		
		$this->addSelect("sch_year", array(
			"name" => "y",
			"options" => range($this->y - 1, $this->y + 2),
			"selected" => $this->y,
			"attr:id" => "year_select",
			"attr:onchange" => "redirectAfterSelectOfSch()"
		));
		
		$this->addSelect("sch_month", array(
			"name" => "m",
			"options" => range(1, 12),
			"selected" => $this->m,
			"attr:id" => "month_select",
			"attr:onchange" => "redirectAfterSelectOfSch()"
		));
		
		$this->addLabel("calendar", array(
			"html" => SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Reserve.CalendarLogic", array("itemId" => $this->itemId))->build($this->y, $this->m)
		));
		
		$this->addLabel("calendar_css", array(
			"html" => file_get_contents(SOY2::RootDir() . "module/plugins/reserve_calendar/css/calendar.css")
		));
	}
	
	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
?>