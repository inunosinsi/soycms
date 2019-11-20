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
			"html" => self::buildUsabledReplaceWordsList()
		));

		self::buildForm();
	}

	private function buildUsabledReplaceWordsList(){
		$html = array();
		$html[] = "<table class=\"form_list\">";
		$html[] = "<caption>使用できる置換文字列</caption>";
		$html[] = "<thead><tr><th>置換文字列</th><th>種類</th></tr></thead>";
		$html[] = "<tbody>";
		foreach(ShippingScheduleUtil::getUsabledReplaceWords() as $k => $w){
			$html[] = "<tr>";
			$html[] = "<td>##" . $k . "##</td>";
			$html[] = "<td>" . $w . "</td>";
			$html[] = "</tr>";
		}
		$html[] = "</tbody>";
		$html[] = "</table>";
		return implode("\n", $html);
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
