<?php

class TrackingMoreConfigFormPage extends WebPage {

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.tracking_more.util.TrackingMoreUtil");
		SOY2::import("util.SOYShopPluginUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			TrackingMoreUtil::saveConfig($_POST["Config"]);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		//伝票番号プラグインのインストールの有無を表示
		DisplayPlugin::toggle("no_installed_slip_number_plugin", !SOYShopPluginUtil::checkIsActive("slip_number"));

		$config = TrackingMoreUtil::getConfig();

		$this->addForm("form");

		$this->addInput("api_key", array(
			"name" => "Config[key]",
			"value" => (isset($config["key"])) ? $config["key"] : ""
		));

		$this->addInput("try_count", array(
			"name" => "Config[try]",
			"value" => (isset($config["try"])) ? (int)$config["try"] : 20
		));

		//出荷予定日があれば
		DisplayPlugin::toggle("use_shipping_date", SOY2Logic::createInstance("module.plugins.tracking_more.logic.TrackLogic")->useShippingDate());

		$this->addInput("start", array(
			"name" => "Config[start]",
			"value" => (isset($config["start"])) ? (int)$config["start"] : 1,
			"style" => "width:50px;"
		));

		$this->addInput("end", array(
			"name" => "Config[end]",
			"value" => (isset($config["end"])) ? (int)$config["end"] : 3,
			"style" => "width:50px;"
		));

		$this->addLabel("job_path", array(
			"text" => self::buildPath(). " " . SOYSHOP_ID
		));

		$this->addLabel("site_id", array(
			"text" => SOYSHOP_ID
		));
	}

	private function buildPath(){
		return dirname(dirname(__FILE__)) . "/job/exe.php";
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
