<?php
class CalendarExpandSeatItemCustomField extends SOYShopItemCustomFieldBase{

	function doPost(SOYShop_Item $item){}

	/**
	 * 管理画面側の商品詳細画面でフォームを表示します。
	 * @param object SOYShop_Item
	 * @return string html
	 */
	function getForm(SOYShop_Item $item){}

	/**
	 * 公開側のblock:id="item"で囲まれた箇所にフォームを出力する
	 * @param object htmlObj, object SOYShop_Item
	 */
	function onOutput($htmlObj, SOYShop_Item $item){
		list($low, $high) = (is_numeric($item->getId())) ? self::logic()->getLowPriceAndHighPriceByItemId($item->getId()) : array(0, 0);
		
		$htmlObj->addLabel("child_price_min", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => number_format((int)$low)
		));

		$htmlObj->addLabel("child_price_max", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => number_format((int)$high)
		));
	}

	/**
	 * 管理画面側で商品情報を削除した時にオプション設定も一緒に削除する
	 * @param integer id
	 */
	function onDelete(int $itemId){}

	private function logic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("module.plugins.calendar_expand_seat.logic.Schedule.ChildPriceLogic");
		return $logic;
	}
}

SOYShopPlugin::extension("soyshop.item.customfield", "calendar_expand_seat", "CalendarExpandSeatItemCustomField");
