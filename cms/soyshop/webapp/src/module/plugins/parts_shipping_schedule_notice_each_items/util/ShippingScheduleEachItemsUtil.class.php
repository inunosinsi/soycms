<?php

class ShippingScheduleEachItemsUtil {

	public static function getTemplates(){
		return SOYShop_DataSets::get("parts_shipping_schedule_notice_each_items.templates", array());
	}

	public static function saveTemplates($values){
		SOYShop_DataSets::put("parts_shipping_schedule_notice_each_items.templates", $values);
	}

	public static function getConfig($itemId){
		return SOYShop_DataSets::get("parts_shipping_schedule_notice_each_items_" . $itemId . ".config", array());
	}

	public static function save($values, $itemId){
		SOYShop_DataSets::put("parts_shipping_schedule_notice_each_items_" . $itemId . ".config", $values);
	}
}
