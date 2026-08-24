<?php

class StructuredDataConfigPage extends WebPage {

	private $pluginObj;

	function __construct(){
		SOY2::import("site_include.plugin.structured_data.util.StructuredDataUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			StructuredDataUtil::saveConfig($_POST["Config"]);
			CMSPlugin::redirectConfigPage();
		}
	}

	function execute(){
		parent::__construct();

		$this->addForm("form");

		$cnf = StructuredDataUtil::getConfig();

		// @ToDo 複数人に対応する必要があるかも
		for($i = 0; $i < 1; $i++){
			$this->addInput("author_name_".$i, array(
				"name" => "Config[author][]",
				"value" => (isset($cnf["author"][$i])) ? trim($cnf["author"][$i]) : ""
			));
		}
	}

	function setPluginObj(StructuredDataPlugin $pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
