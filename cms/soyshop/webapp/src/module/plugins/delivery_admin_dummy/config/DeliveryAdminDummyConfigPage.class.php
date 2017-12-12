<?php

class DeliveryAdminDummyConfigPage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.delivery_admin_dummy.util.DeliveryAdminDummyUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			$values = (isset($_POST["Config"])) ? $_POST["Config"] : array();
			DeliveryAdminDummyUtil::saveConfig($values);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();
		$config = DeliveryAdminDummyUtil::getConfig();

		$this->addForm("form");

		$this->addCheckBox("show_description", array(
			"name" => "Config[show_description]",
			"value" => 1,
			"selected" => (isset($config["show_description"]) && (int)$config["show_description"] === 1),
			"label" => "配送選択画面で各種フォームを表示する"
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
