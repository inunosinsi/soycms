<?php

class CalendarExpandSeatConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.calendar_expand_seat.config.ExpandSeatConfigPage");
		$form = SOY2HTMLFactory::createInstance("ExpandSeatConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 * 拡張設定に表示されたモジュールのタイトルを表示する
	 */
	function getConfigPageTitle(){
		return "予約カレンダー人数指定拡張プラグイン";
	}

}
SOYShopPlugin::extension("soyshop.config", "calendar_expand_seat", "CalendarExpandSeatConfig");
