<?php

class DateLogic extends SOY2LogicBase {

	function __construct(){
		SOY2::import("module.plugins.reserve_calendar.domain.SOYShopReserveCalendar_ScheduleSearchDAO");
	}

	function getSchedulePeriodByItemId(int $itemId){
		static $list;
		if(is_null($list)) $list = array();
		if($itemId === 0) return array(null, null);
		if(isset($list[$itemId])) return $list[$itemId];

		$start = null;
		$end = null;

		SOY2::import("util.SOYShopPluginUtil");
		if(SOYShopPluginUtil::checkIsActive("custom_search_field")){
			SOY2::import("module.plugins.custom_search_field.util.CustomSearchFieldUtil");
			$params = CustomSearchFieldUtil::getParameter("c_search");

			if(isset($params["reserve_calendar_start"]) && strlen($params["reserve_calendar_start"])){
				$start = CustomSearchFieldUtil::str2timestamp($params["reserve_calendar_start"], CustomSearchFieldUtil::CONVERT_MODE_START);
			}

			if(isset($params["reserve_calendar_end"]) && strlen($params["reserve_calendar_end"])){
				$end = CustomSearchFieldUtil::str2timestamp($params["reserve_calendar_end"], CustomSearchFieldUtil::CONVERT_MODE_END);
			}
		}

		list($min, $max) = self::dao()->getSchedulePeriodByItemId($itemId, $start, $end);
		$list[$itemId] = array($min, $max);
		return $list[$itemId];
	}

	private function dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("SOYShopReserveCalendar_ScheduleSearchDAO");
		return $dao;
	}
}
