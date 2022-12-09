<?php
class CalendarConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.parts_calendar.config.PartsCalendarConfigFormPage");
		$form = SOY2HTMLFactory::createInstance("PartsCalendarConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "営業日カレンダープラグイン";
	}

}
SOYShopPlugin::extension("soyshop.config", "parts_calendar", "CalendarConfig");
