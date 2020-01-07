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

	public static function buildUsabledReplaceWordsList(){
		$html = array();
		$html[] = "<table class=\"form_list\">";
		$html[] = "<caption>使用できる置換文字列</caption>";
		$html[] = "<thead><tr><th>置換文字列</th><th>種類</th></tr></thead>";
		$html[] = "<tbody>";
		SOY2::import("module.plugins.parts_shipping_schedule_notice.util.ShippingScheduleUtil");
		foreach(ShippingScheduleUtil::getUsabledReplaceWords() as $k => $w){
			$html[] = "<tr>";
			$html[] = "<td>##" . $k . "##</td>";
			$html[] = "<td>" . $w . "</td>";
			$html[] = "</tr>";
		}
		$html[] = "</tbody>";
		$html[] = "</table>";
		return implode("\n", $html);
	}
}
