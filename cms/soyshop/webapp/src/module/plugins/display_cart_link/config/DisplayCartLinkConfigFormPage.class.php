<?php

class DisplayCartLinkConfigFormPage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.display_cart_link.util.DisplayCartLinkUtil");
	}

	function doPost(){

		if(soy2_check_token()){
			$config = (isset($_POST["Config"])) ? $_POST["Config"] : array();
			$config["limitation"] = (isset($config["limitation"])) ? (int)$config["limitation"] : 0;

			DisplayCartLinkUtil::saveConfig($config);

			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$this->addForm("form");

		$config = DisplayCartLinkUtil::getConfig();

		$this->addCheckBox("limitation_cart", array(
			"name" => "Config[limitation]",
			"value" => 1,
			"selected" => (isset($config["limitation"]) && $config["limitation"] == 1),
			"label" => "カートを非表示にした場合、カートに入れるリンクの直打ちを拒否する"
		));

	}

	function setConfigObj($configObj) {
		$this->configObj = $configObj;
	}
}
?>
