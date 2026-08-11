<?php

class AccessRestrictionConfigPage extends WebPage {

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.access_restriction.util.AccessRestrictionUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			if(isset($_POST["register"])){
				AccessRestrictionUtil::registerBrowser();
				$this->configObj->redirect("successed");
			}

			if(isset($_POST["release"])){
				AccessRestrictionUtil::releaseBrowser();
				$this->configObj->redirect("released");
			}

			if(isset($_POST["update"])){
				$arr = (isset($_POST["display_config"]) && is_array($_POST["display_config"])) ? $_POST["display_config"] : array();
				AccessRestrictionUtil::savePageDisplayConfig($arr);
				AccessRestrictionUtil::saveConfig($_POST["Config"]);
				$this->configObj->redirect("updated");
			}

		}
	}

	function execute(){
		parent::__construct();

		self::_buildRegisterBrowserArea();
		self::_buildPageConfigArea();
	}

	private function _buildRegisterBrowserArea(){
		DisplayPlugin::toggle("successed", isset($_GET["successed"]));
		DisplayPlugin::toggle("released", isset($_GET["released"]));

		$on = AccessRestrictionUtil::checkBrowser();
		DisplayPlugin::toggle("no_register", !$on);
		DisplayPlugin::toggle("is_register", $on);

		$this->addForm("register_form");
	}

	private function _buildPageConfigArea(){
		$cnf = AccessRestrictionUtil::getConfig();

		$this->addForm("form");

		$this->addInput("day", array(
			"name" => "Config[day]",
			"value" => (isset($cnf["day"]) && is_numeric($cnf["day"])) ? (int)$cnf["day"] : 3
		));

		SOY2::import("module.plugins.x_html_cache.component.PageListComponent");
		$this->createAdd("page_list", "PageListComponent", array(
			"list" => soyshop_get_page_list(),
			"displayConfig" => AccessRestrictionUtil::getPageDisplayConfig()
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
