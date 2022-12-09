<?php

class CalendarExpandSmartConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.calendar_expand_smart.config.ExpandSmartConfigPage");
		$form = SOY2HTMLFactory::createInstance("ExpandSmartConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 * 拡張設定に表示されたモジュールのタイトルを表示する
	 */
	function getConfigPageTitle(){
		return "予約カレンダースマホ拡張プラグイン";
	}

}
SOYShopPlugin::extension("soyshop.config", "calendar_expand_smart", "CalendarExpandSmartConfig");
