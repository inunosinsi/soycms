<?php

class BusinessCalendarUtil {

	/**
	 * 他のプラグインで営業日のチェックができるようにするメソッド
	 * @param int timestamp
	 * @return bool 営業日であればtrue
	 */
	public static function isBD(int $timestamp){
		if(!SOYShopPluginUtil::checkIsActive("parts_calendar")) return true;	//プラグインが有効でなければ常に営業日
		return SOY2Logic::createInstance("module.plugins.parts_calendar.logic.CalendarLogic")->isBD(soyshop_shape_timestamp($timestamp));
	}
}