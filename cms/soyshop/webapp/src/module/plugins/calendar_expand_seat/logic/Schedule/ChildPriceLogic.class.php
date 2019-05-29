<?php

class ChildPriceLogic extends SOY2LogicBase {

	function __construct(){
		SOY2::import("module.plugins.reserve_calendar.domain.SOYShopReserveCalendar_SchedulePriceDAO");
	}

	function getLowPriceAndHighPriceByItemId($itemId){
		static $list;
		if(is_null($list)) $list = array();
		if(is_null($itemId) || !is_numeric($itemId)) return array(0, 0);
		if(isset($list[$itemId])) return $list[$itemId];

		//子供料金を調べる
		list($low, $high) = self::dao()->getLowPriceAndHighPriceByItemIdAndFieldId($itemId, "child_price");

		//通常の料金を調べる
		list($pLow, $pHigh) = self::logic()->getLowPriceAndHighPriceByItemId($itemId);

		if($low > $pLow) $low = $pLow;

		$list[$itemId] = array($low, $high);
		return $list[$itemId];
	}

	private function dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("SOYShopReserveCalendar_SchedulePriceDAO");
		return $dao;
	}

	private function logic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Schedule.PriceLogic");
		return $logic;
	}
}
