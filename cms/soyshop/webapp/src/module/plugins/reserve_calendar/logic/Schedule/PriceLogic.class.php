<?php

class PriceLogic extends SOY2LogicBase {

	function __construct(){
		SOY2::import("module.plugins.reserve_calendar.domain.SOYShopReserveCalendar_ScheduleDAO");
	}

	function getLowPriceAndHighPriceByItemId($itemId){
		static $list;
		if(is_null($list)) $list = array();
		if(is_null($itemId) || !is_numeric($itemId)) return array(0, 0);
		if(isset($list[$itemId])) return $list[$itemId];

		list($low, $high) = self::dao()->getLowPriceAndHighPriceByItemId($itemId);
		$list[$itemId] = array($low, $high);
		return $list[$itemId];
	}

	private function dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("SOYShopReserveCalendar_ScheduleDAO");
		return $dao;
	}
}
