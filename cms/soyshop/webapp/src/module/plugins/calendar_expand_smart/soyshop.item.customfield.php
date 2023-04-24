<?php
class CalendarExpandSmartCustomField extends SOYShopItemCustomFieldBase{

	function doPost(SOYShop_Item $item){
		SOY2::import("module.plugins.calendar_expand_smart.util.SmartCalendarUtil");
		if(isset($_POST[SmartCalendarUtil::FIELD_ID]) && is_numeric($_POST[SmartCalendarUtil::FIELD_ID])){
			$attr = soyshop_get_item_attribute_object($item->getId(), SmartCalendarUtil::FIELD_ID);
			$attr->setValue($_POST[SmartCalendarUtil::FIELD_ID]);
			soyshop_save_item_attribute_object($attr);
		}

		if(isset($_POST[SmartCalendarUtil::PAGER_FIELD_ID])){
			$attr = soyshop_get_item_attribute_object($item->getId(), SmartCalendarUtil::PAGER_FIELD_ID);
			$attr->setValue(trim($_POST[SmartCalendarUtil::PAGER_FIELD_ID]));
			soyshop_save_item_attribute_object($attr);
		}
	}

	/**
	 * 管理画面側の商品詳細画面でフォームを表示します。
	 * @param object SOYShop_Item
	 * @return string html
	 */
	function getForm(SOYShop_Item $item){
		SOY2::import("module.plugins.calendar_expand_smart.util.SmartCalendarUtil");
		
		//リンクを表示
		$html = array();
		
		$html[] = "<br>";
		$html[] = "<div class=\"alert alert-info\">予約カレンダースマホ拡張設定</div>";
		$html[] = "<div class=\"form-group\">";
		$html[] = "<label>表示日数</label>";
		$html[] = "<div class=\"form-inline\">";
		$html[] = "<input type=\"number\" class=\"form-control\" name=\"".SmartCalendarUtil::FIELD_ID."\" value=\"".SmartCalendarUtil::getDisplayDayCount($item->getId())."\" style=\"width:120px;\" required=\"required\">日";
		$html[] = "</div>";
		$html[] = "</div>";
		$html[] = "<div class=\"form-group\">";
		$html[] = "<label>ページャ</label>";
		$html[] = "<div class=\"form-inline\">";
		$html[] = "<input type=\"number\" class=\"form-control\" name=\"".SmartCalendarUtil::PAGER_FIELD_ID."\" value=\"".SmartCalendarUtil::getPagerDayCount($item->getId())."\" style=\"width:120px;\">日分表示";
		$html[] = "</div>";
		$html[] = "</div>";
		$html[] = "<div class=\"alert alert-info\">予約カレンダースマホ拡張設定ここまで</div>";
		return implode("\n", $html);
	}

	function onOutput($htmlObj, SOYShop_Item $item){}
}

SOYShopPlugin::extension("soyshop.item.customfield", "calendar_expand_smart", "CalendarExpandSmartCustomField");
