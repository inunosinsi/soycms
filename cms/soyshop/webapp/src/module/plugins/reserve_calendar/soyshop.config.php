<?php

class ReserveCalendarConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		if(isset($_GET["item_id"])){
			//カレンダーの表示
			if(isset($_GET["calendar"])){
				SOY2::import("module.plugins.reserve_calendar.config.Calendar.CalendarFormPage");
				$form = SOY2HTMLFactory::createInstance("CalendarFormPage");

			//定休日の設定
			}else if(isset($_GET["holiday"])){
				SOY2::import("module.plugins.reserve_calendar.config.Calendar.HolidayConfigPage");
				$form = SOY2HTMLFactory::createInstance("HolidayConfigPage");

			//ラベルの設定
			}else if(isset($_GET["label"]) || isset($_GET["remove"])){
				SOY2::import("module.plugins.reserve_calendar.config.Calendar.LabelConfigPage");
				$form = SOY2HTMLFactory::createInstance("LabelConfigPage");
			}else{
				SOY2::import("module.plugins.reserve_calendar.config.Calendar.TagExamplePage");
				$form = SOY2HTMLFactory::createInstance("TagExamplePage");
			}
			$form->setItemId($_GET["item_id"]);

		//設定
		}else{
			SOY2::import("module.plugins.reserve_calendar.config.ReserveCalendarConfigFormPage");
			$form = SOY2HTMLFactory::createInstance("ReserveCalendarConfigFormPage");
		}

		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		if(isset($_GET["item_id"])){
			if(isset($_GET["calendar"])) return "予約カレンダーの設定";
			if(isset($_GET["holiday"])) return "予約カレンダーの定休日設定";
			if(isset($_GET["label"])) return "予約カレンダーのラベル設定";
			if(isset($_GET["tag"])) return "予約カレンダー - テンプレートへの記述例";
		}else{
			return "予約カレンダーの設定";
		}
	}
}
SOYShopPlugin::extension("soyshop.config", "reserve_calendar", "ReserveCalendarConfig");
