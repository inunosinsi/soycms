<?php

class ReserveCalendarInfoPage extends WebPage{
	
	private $configObj;
	
	function __construct(){
		SOY2::import("module.plugins.reserve_calendar.component.admin.NewReserveInfoListComponent");
	}
	
	function execute(){
		parent::__construct();
		
		$reserves = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Reserve.ReserveLogic")->getReservedSchedules(time());
		
		$resCnt = count($reserves);
		DisplayPlugin::toggle("has_reserve", ($resCnt > 0));
		DisplayPlugin::toggle("more_reserve", ($resCnt > 15));
		DisplayPlugin::toggle("no_reserve", ($resCnt === 0));
				
		$this->createAdd("reserve_list", "NewReserveInfoListComponent", array(
			"list" => $reserves,
			"labels" => SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Calendar.LabelLogic")->getLabelListAll()
		));
	}
	
	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
?>