<?php

class ExpandSmartConfigPage extends WebPage {

	private $configObj;

	function __construct(){}

	function execute(){
		parent::__construct();

		$isInstalled = SOYShopPluginUtil::checkIsActive("reserve_calendar");
		DisplayPlugin::toggle("no_installed_reserve_calendar", !$isInstalled);
		DisplayPlugin::toggle("installed_reserve_calendar", $isInstalled);

		$this->addLabel("bootstrap_sample", array(
			"html" => self::getSampleHtml()
		));
	}

	private function getSampleHtml(){
		$html = file_get_contents(dirname(dirname(__FILE__)) . "/sample/calendar.html");
		$html = str_replace("<", "&lt;", $html);
		$html = str_replace(">", "&gt;", $html);
		return $html;
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
