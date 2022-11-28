<?php
SOY2::import("module.plugins.fixed_form_module.util.FixedFormModuleUtil");
class FixedFormModuleConfigPage extends WebPage {

	private $configObj;

	function __construct(){

	}

	function doPost(){
		if(soy2_check_token() && isset($_POST["Config"])){
			FixedFormModuleUtil::saveConfig($_POST["Config"]);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$cnf = FixedFormModuleUtil::getConfig();

		$this->addForm("form");

		$this->addInput("form_name", array(
			"name" => "Config[form_name]",
			"value" => (isset($cnf["form_name"]) && strlen($cnf["form_name"])) ? $cnf["form_name"] : "",
			"attr:placeholder" => "shop:module=\"fixed_form_module\"内で実行するモジュールの選択",
			"style" => "width:95%;"
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
