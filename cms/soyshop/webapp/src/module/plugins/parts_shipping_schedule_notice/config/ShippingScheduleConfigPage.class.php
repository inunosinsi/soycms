<?php

class ShippingScheduleConfigPage extends WebPage {

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.parts_shipping_schedule_notice.util.ShippingScheduleUtil");
		SOY2::import("module.plugins.parts_shipping_schedule_notice.component.ScheduleNoticeListComponent");
	}

	function doPost(){
		if(soy2_check_token() && isset($_POST["Config"])){
			ShippingScheduleUtil::save($_POST["Config"]);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$isInstalledCalendarPlugin = SOYShopPluginUtil::checkIsActive("parts_calendar");
		DisplayPlugin::toggle("no_installed_calendar_plugin", !$isInstalledCalendarPlugin);
		DisplayPlugin::toggle("installed_calendar_plugin", $isInstalledCalendarPlugin);

		$this->addLabel("replace_words_list", array(
			"html" => ShippingScheduleUtil::buildUsabledReplaceWordsList()
		));

		self::buildForm();
	}

	private function buildForm(){
		$this->addForm("form");

		$this->createAdd("notice_list", "ScheduleNoticeListComponent", array(
			"list" => ShippingScheduleUtil::getPatterns(),
			"config" => ShippingScheduleUtil::getConfig()
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
