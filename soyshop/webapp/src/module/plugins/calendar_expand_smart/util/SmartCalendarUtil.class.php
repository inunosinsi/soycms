<?php
class SmartCalendarUtil {

	const FIELD_ID = "smart_calendar_day_count";
	const PAGER_FIELD_ID = "smart_calendar_pager";

	/**
	 * @param int
	 * @return int
	 */
	public static function getDisplayDayCount(int $itemId){
		$v = soyshop_get_item_attribute_value($itemId, self::FIELD_ID);
		return (is_numeric($v)) ? (int)$v : 14;
	}

	/**
	 * @param int
	 * @return int|null
	 */
	public static function getPagerDayCount(int $itemId){
		$v = soyshop_get_item_attribute_value($itemId, self::PAGER_FIELD_ID);
		return (is_numeric($v)) ? (int)$v : null;
	}
}