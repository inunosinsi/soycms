<?php

class FixedPointGrantConfigFormPage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.fixed_point_grant.util.FixedPointGrantUtil");
	}

	function doPost(){
    	if(soy2_check_token() && isset($_POST["Config"])){
    		FixedPointGrantUtil::saveConfig($_POST["Config"]);
			$this->configObj->redirect("updated");
    	}
		$this->configObj->redirect("error");
    }

	function execute(){
		parent::__construct();

		//ポイント制設定プラグインのインストールは必須
		//簡易ポイント付与プラグインとの併用ができない
		DisplayPlugin::toggle("uninstalled_point_base_plugin", !SOYShopPluginUtil::checkIsActive("common_point_base"));
		DisplayPlugin::toggle("installed_point_grant_plugin", SOYShopPluginUtil::checkIsActive("common_point_grant"));

		$config = FixedPointGrantUtil::getConfig();

    	DisplayPlugin::toggle("error", isset($_GET["error"]));

		$this->addForm("form");

		$this->addInput("fixed_point", array(
			"name" => "Config[fixed_point]",
			"value" => (isset($config["fixed_point"])) ? (int)$config["fixed_point"] : 0,
			"style" => "text-align:right;width:50px;"
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
