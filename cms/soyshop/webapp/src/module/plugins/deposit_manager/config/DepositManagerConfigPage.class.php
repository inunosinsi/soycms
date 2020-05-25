<?php

class DepositManagerConfigPage extends WebPage {

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.deposit_manager.util.DepositManagerUtil");
		SOY2::import("module.plugins.deposit_manager.component.DepositSubjectListComponent");
	}

	function doPost(){
		if(soy2_check_token()){
			if(isset($_POST["Add"]) && strlen(trim($_POST["Add"]))){
				if(DepositManagerUtil::addSubject(trim($_POST["Add"]))){
					$this->configObj->redirect("updated");
				}
			}

			if(isset($_POST["DisplayOrder"]) && count($_POST["DisplayOrder"])){
				DepositManagerUtil::changeDisplayOrder($_POST["DisplayOrder"]);
				$this->configObj->redirect("updated");
			}
		}

		$this->configObj->redirect("failed");
	}

	function execute(){
		parent::__construct();

		if(isset($_GET["subject_id"]) && is_numeric($_GET["subject_id"])){
			if(DepositManagerUtil::removeSubjectById($_GET["subject_id"])){
				$this->configObj->redirect("removed");
			}
		}

		DisplayPlugin::toggle("removed", isset($_GET["removed"]));
		DisplayPlugin::toggle("failed", isset($_GET["failed"]));

		$this->addForm("form");

		$this->addInput("deposit_subject_input", array(
			"name" => "Add",
			"attr:required" => "required"
		));

		$this->addForm("order_form");

		$this->createAdd("subject_list", "DepositSubjectListComponent", array(
			"list" => DepositManagerUtil::getSubjectList()
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
