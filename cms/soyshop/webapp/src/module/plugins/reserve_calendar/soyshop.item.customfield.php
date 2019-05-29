<?php
class ReserveCalendarCustomField extends SOYShopItemCustomFieldBase{

	function doPost(SOYShop_Item $item){}

	/**
	 * 管理画面側の商品詳細画面でフォームを表示します。
	 * @param object SOYShop_Item
	 * @return string html
	 */
	function getForm(SOYShop_Item $item){
		//リンクを表示
		$html = array();
		$html[] = "<dt>予約カレンダースケジュール</dt>";
		$html[] = "<dd><a href=\"" . SOY2PageController::createLink("Config.Detail?plugin=reserve_calendar&calendar&item_id=" . $item->getId()) . "\" class=\"button\">予約カレンダーを開く</a></dd>";
		$html[] = "<dd><a href=\"" . SOY2PageController::createLink("Config.Detail?plugin=reserve_calendar&holiday&item_id=" . $item->getId()) . "\" class=\"button\">定休日の設定を開く</a></dd>";
		$html[] = "<dd><a href=\"" . SOY2PageController::createLink("Config.Detail?plugin=reserve_calendar&label&item_id=" . $item->getId()) . "\" class=\"button\">予定で使用するラベル設定を開く</a></dd>";
		$html[] = "<dd><a href=\"" . SOY2PageController::createLink("Config.Detail?plugin=reserve_calendar&tag&item_id=" . $item->getId()) . "\" class=\"button\">テンプレートへの記述例</a></dd>";
		return implode("\n", $html);
	}

	/**
	 * 公開側のblock:id="item"で囲まれた箇所にフォームを出力する
	 * @param object htmlObj, object SOYShop_Item
	 */
	function onOutput($htmlObj, SOYShop_Item $item){
		list($low, $high) = self::logic()->getLowPriceAndHighPriceByItemId($item->getId());
		
		$htmlObj->addLabel("schedule_price_min", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => number_format($low)
		));

		$htmlObj->addLabel("schedule_price_max", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => number_format($high)
		));
	}

	/**
	 * 管理画面側で商品情報を削除した時にオプション設定も一緒に削除する
	 * @param integer id
	 */
	function onDelete($id){}

	private function logic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Schedule.PriceLogic");
		return $logic;
	}
}

SOYShopPlugin::extension("soyshop.item.customfield", "reserve_calendar", "ReserveCalendarCustomField");
