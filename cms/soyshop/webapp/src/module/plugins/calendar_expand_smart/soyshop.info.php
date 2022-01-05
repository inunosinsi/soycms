<?php
/*
 */
class CalendarExpandSmartInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){
		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=calendar_expand_smart") . '">予約カレンダースマホ拡張プラグイン</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "calendar_expand_smart", "CalendarExpandSmartInfo");
