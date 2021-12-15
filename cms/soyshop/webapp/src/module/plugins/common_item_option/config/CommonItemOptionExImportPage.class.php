<?php

class CommonItemOptionExImportPage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.common_item_option.util.ItemOptionUtil");
	}

	function doPost(){
		if(soy2_check_token() && isset($_POST["import"]) && strlen(trim($_POST["configure"])) > 0){
			$value = trim($_POST["configure"]);
			$value = base64_decode($value);

			$opts = soy2_unserialize((string)$value);
			if(!is_array($opts)) SOY2PageController::jump("Config.Detail?plugin=common_item_option&import&failed");

			ItemOptionUtil::saveOptions($opts);
			SOY2PageController::jump("Config.Detail?plugin=common_item_option&updated");
			exit;
		}
	}

	function execute(){
		$opts = ItemOptionUtil::getOptions();
		if(!count($opts)) SOY2PageController::jump("Config.Detail?plugin=common_item_option");

		parent::__construct();

		$this->addForm("form");
		$value = base64_encode(soy2_serialize($opts));

		$this->addTextArea("export_value", array(
			"value" => $value,
			"style" => "height:200px;",
			"onclick" => "this.select();"
		));

		$this->addTextArea("import_value", array(
			"name" => "configure",
			"style" => "height:200px;"
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
