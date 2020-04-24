<?php
/*
 */
class CalendarModuleInfo extends SOYShopInfoPageBase{

	function getPage($active = true){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=parts_calendar").'">営業日カレンダープラグインの設定</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info","parts_calendar","CalendarModuleInfo");
