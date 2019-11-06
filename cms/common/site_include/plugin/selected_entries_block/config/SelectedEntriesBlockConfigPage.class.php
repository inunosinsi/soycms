<?php

class SelectedEntriesBlockConfigPage extends WebPage {

	private $pluginObj;

	function __construct(){

	}

	function doPost(){
		if(soy2_check_token()){
			$this->pluginObj->setItemName($_POST["item_name"]);
			CMSPlugin::savePluginConfig($this->pluginObj->getId(), $this->pluginObj);
			CMSPlugin::redirectConfigPage();
		}
	}

	function execute(){
		parent::__construct();

		$this->addForm("form");

		$this->addInput("item_name", array(
			"name" => "item_name",
			"value" => $this->pluginObj->getItemName()
		));
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
