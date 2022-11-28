<?php
/*
 */
class CalendarExpandSeatInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){
		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=calendar_expand_seat") . '">予約カレンダー人数指定拡張プラグイン</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "calendar_expand_seat", "CalendarExpandSeatInfo");
