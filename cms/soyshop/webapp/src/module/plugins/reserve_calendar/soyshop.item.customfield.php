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
		$html[] = "<div class=\"alert alert-info\">予約カレンダースケジュール</div>";
		$html[] = "<ul style=\"list-style-type:none;\">";
		$html[] = "	<li><a href=\"" . SOY2PageController::createLink("Config.Detail?plugin=reserve_calendar&calendar&item_id=" . $item->getId()) . "\" class=\"btn btn-default\">予約カレンダーを開く</a></li>";
		$html[] = "	<li style=\"margin:12px 0;\"><a href=\"" . SOY2PageController::createLink("Config.Detail?plugin=reserve_calendar&holiday&item_id=" . $item->getId()) . "\" class=\"btn btn-default\">定休日の設定を開く</a></li>";
		$html[] = "	<li style=\"margin:12px 0;\"><a href=\"" . SOY2PageController::createLink("Config.Detail?plugin=reserve_calendar&label&item_id=" . $item->getId()) . "\" class=\"btn btn-default\">予定で使用するラベル設定を開く</a></li>";
		$html[] = "	<li style=\"margin:12px 0;\"><a href=\"" . SOY2PageController::createLink("Config.Detail?plugin=reserve_calendar&tag&item_id=" . $item->getId()) . "\" class=\"btn btn-default\">テンプレートへの記述例</a></li>";
		$html[] = "</ul>";
		$html[] = "<div class=\"alert alert-info\">予約カレンダースケジュールここまで</div>";
		return implode("\n", $html);
	}

	/**
	 * 公開側のblock:id="item"で囲まれた箇所にフォームを出力する
	 * @param object htmlObj, object SOYShop_Item
	 */
	function onOutput($htmlObj, SOYShop_Item $item){
		//最安値と最高値
		list($low, $high) = self::logic()->getLowPriceAndHighPriceByItemId($item->getId());

		$htmlObj->addLabel("schedule_price_min", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => number_format($low)
		));

		$htmlObj->addLabel("schedule_price_max", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => number_format($high)
		));

		//スケジュールの日付の範囲
		list($start, $end) = self::dateLogic()->getSchedulePeriodByItemId($item->getId());
		$htmlObj->addLabel("schedule_date_start", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => (isset($start) && is_numeric($start)) ? date("Y-m-d", $start) : ""
		));

		$htmlObj->addLabel("schedule_date_end", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => (isset($end) && is_numeric($end)) ? date("Y-m-d", $end) : ""
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

	private function dateLogic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Schedule.DateLogic");
		return $logic;
	}
}

SOYShopPlugin::extension("soyshop.item.customfield", "reserve_calendar", "ReserveCalendarCustomField");
