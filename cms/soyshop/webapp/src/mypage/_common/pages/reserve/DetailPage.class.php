<?php

class DetailPage extends MainMyPagePageBase{

	function __construct($args){
		$this->checkIsLoggedIn(); //ログインチェック

		if(!isset($args[0]) || !is_numeric($args[0])) $this->jumpToTop();

		//予約カレンダーを有効にしていて、bootstrapテンプレートを使用している時のみ表示
		if(!SOYShopPluginUtil::checkIsActive("reserve_calendar")) $this->jumpToTop();
		if(soyshop_get_mypage_id() != "bootstrap" && (!defined("MYPAGE_EXTEND_BOOTSTRAP") || !MYPAGE_EXTEND_BOOTSTRAP)) $this->jumpToTop();

		$resId = (int)$args[0];
		$user = $this->getUser();

		//予約が正しいか調べる
		if(!SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Reserve.ReserveLogic")->checkReserveByUserId($resId, $user->getId())) $this->jumpToTop();

		parent::__construct();

        $this->addLabel("user_name", array(
            "text" => $user->getName()
        ));

		SOY2::import("module.plugins.reserve_calendar.domain.SOYShopReserveCalendar_ScheduleDAO");
		$schedule = SOY2DAOFactory::create("SOYShopReserveCalendar_ScheduleDAO")->getScheduleByReserveId($resId);
		self::buildScheduleInfoArea($schedule);
	}

	private function buildScheduleInfoArea(SOYShopReserveCalendar_Schedule $schedule){

		$item = soyshop_get_item_object($schedule->getItemId());
		$this->addLink("item_name", array(
			"link" => soyshop_get_item_detail_link($item),
			"text" => $item->getName()
		));

		$this->addLabel("schedule", array(
			"text" => $schedule->getYear() . "-" . $schedule->getMonth() . "-" . $schedule->getDay() . " " . SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Calendar.LabelLogic")->getLabelNameById($schedule->getLabelId())
		));

		$this->addLabel("price", array(
			"text" => number_format($schedule->getPrice())
		));

		//金額に関する拡張ポイント
		SOY2::import("module.plugins.reserve_calendar.component.admin.PriceListComponent");
		$this->createAdd("price_list", "PriceListComponent", array(
			"list" => self::getExtPrices($schedule->getId())
		));

	}

	private function getExtPrices($scheduleId){
		SOYShopPlugin::load("soyshop.add.price.on.calendar");
		$array = SOYShopPlugin::invoke("soyshop.add.price.on.calendar", array(
			"mode" => "list",
			"scheduleId" => $scheduleId
		))->getList();

		if(!is_array($array) || !count($array)) return array();

		$list = array();
		foreach($array as $values){
			$list[] = $values;
		}
		return $list;
	}
}
