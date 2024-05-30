<?php
class CalendarAdmin extends SOYShopAdminBase{

	function execute(){
		SOY2Logic::createInstance("module.plugins.parts_calendar.logic.SOYCalendarConnectLogic")->insertSchedule();
	}
}
SOYShopPlugin::extension("soyshop.admin", "parts_calendar", "CalendarAdmin");
