<?php

class BusinessCalendarUtil {

	/**
	 * 他のプラグインで営業日のチェックができるようにするメソッド
	 * @param int timestamp
	 * @return bool 営業日であればtrue
	 */
	public static function isBD(int $timestamp){
		$timestamp = soyshop_shape_timestamp($timestamp);
		return SOY2Logic::createInstance("module.plugins.parts_calendar.logic.CalendarLogic")->isBD($timestamp);
	}
}