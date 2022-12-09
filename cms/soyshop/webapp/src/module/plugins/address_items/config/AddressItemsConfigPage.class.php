<?php

class AddressItemsConfigPage extends WebPage {

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.address_items.util.AddressItemsUtil");
		SOY2::import("module.plugins.address_items.component.AddressItemsListComponent");
	}

	function doPost(){
		if(soy2_check_token()){
			AddressItemsUtil::save($_POST["Config"]);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$this->addForm("form");

		$this->createAdd("address_list", "AddressItemsListComponent", array(
			"list" => AddressItemsUtil::getConfig()
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}