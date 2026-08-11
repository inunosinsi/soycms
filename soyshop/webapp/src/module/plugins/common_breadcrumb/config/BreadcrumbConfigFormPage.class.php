<?php

class BreadcrumbConfigFormPage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.common_breadcrumb.util.BreadcrumbUtil");
	}

	function doPost(){

		if(soy2_check_token()){

			$config = (isset($_POST["Config"])) ? $_POST["Config"] : null;
			$config["displayChild"] = (isset($_POST["Config"]["displayChild"])) ? (int)$_POST["Config"]["displayChild"] : 0;

			BreadcrumbUtil::saveConfig($config);

			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$config = BreadcrumbUtil::getConfig();

		$this->addForm("form");

		$this->addCheckBox("display_child", array(
			"name" => "Config[displayChild]",
			"value" => 1,
			"selected" => (isset($config["displayChild"]) && $config["displayChild"] == 1),
			"label" => "パンくずリストに子商品を表示する"
		));
	}

	function setConfigObj($configObj) {
		$this->configObj = $configObj;
	}
}
?>
