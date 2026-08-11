<?php

class InsertStringTemplateConfigPage extends WebPage {

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.order_mail_insert_template.util.InsertStringTemplateUtil");
		SOY2::import("module.plugins.order_mail_insert_template.component.TemplateListComponent");
	}

	function doPost(){
		if(soy2_check_token()){
			$config = InsertStringTemplateUtil::getConfig();

			if(isset($_POST["add"]) && strlen(trim($_POST["add"]["id"])) && strlen(trim($_POST["add"]["label"]))){
				$config[trim($_POST["add"]["id"])] = trim($_POST["add"]["label"]);
				InsertStringTemplateUtil::saveConfig($config);
				$this->configObj->redirect("registerd");
			}

			if(isset($_POST["Edit"]) && count($_POST["Edit"])){
				foreach($_POST["Edit"] as $fieldId => $txt){
					InsertStringTemplateUtil::saveTextByFieldId($fieldId, $txt);
				}
				$this->configObj->redirect("updated");
			}
		}

		$this->configObj->redirect("error");
	}

	function execute(){
		if(isset($_GET["remove"])){
			if(soy2_check_token()){
				$config = InsertStringTemplateUtil::getConfig();
				if(isset($config[$_GET["remove"]])){
					unset($config[$_GET["remove"]]);
					InsertStringTemplateUtil::saveConfig($config);
					$this->configObj->redirect("removed");
				}
			}
		}

		parent::__construct();

		DisplayPlugin::toggle("error", isset($_GET["error"]));
		DisplayPlugin::toggle("registerd", isset($_GET["registerd"]));
		DisplayPlugin::toggle("removed", isset($_GET["removed"]));

		self::buildEditForm();
		self::buildNewForm();
	}

	private function buildEditForm(){
		$config = InsertStringTemplateUtil::getConfig();
		DisplayPlugin::toggle("template", count($config));

		$this->addForm("form");

		$this->createAdd("template_list", "TemplateListComponent",  array(
			"list" => $config
		));
	}

	private function buildNewForm(){
		$this->addForm("add_form");
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
