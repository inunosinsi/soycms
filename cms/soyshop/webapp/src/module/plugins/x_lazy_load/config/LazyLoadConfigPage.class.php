<?php

class LazyLoadConfigPage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.x_lazy_load.util.LazyLoadUtil");
	}

	function doPost(){

		if(soy2_check_token()){
			LazyLoadUtil::saveConfig($_POST["Config"]);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$cnf = LazyLoadUtil::getConfig();

		$this->addForm("form");

		$this->addInput("count", array(
			"name" => "Config[count]",
			"value" => (int)$cnf["count"],
			"style" => "width:100px"
		));

		$this->addLabel("shop_id", array(
			"text" => SOYSHOP_ID
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
