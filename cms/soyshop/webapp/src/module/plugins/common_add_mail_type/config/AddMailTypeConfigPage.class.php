<?php

class AddMailTypeConfigPage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2::imports("module.plugins.common_add_mail_type.component.*");
		SOY2::import("module.plugins.common_add_mail_type.util.AddMailTypeUtil");
	}

	function doPost(){

		if(soy2_check_token() && isset($_POST["created"])){
			$values = array();
			$values["id"] = trim(htmlspecialchars($_POST["mail_id"], ENT_QUOTES, "UTF-8"));
			$values["title"] = trim(htmlspecialchars($_POST["mail_title"], ENT_QUOTES, "UTF-8"));


			$configs = AddMailTypeUtil::getConfig($_POST["mail_type"]);
			$configs[$values["id"]] = $values;

			AddMailTypeUtil::saveConfig($configs, $_POST["mail_type"]);

			$this->configObj->redirect("created");
		}

		//move
		if(isset($_POST["move_up"]) || isset($_POST["move_down"])){
			$fieldId = $_POST["field_id"];

			$configs = AddMailTypeUtil::getConfig();

			$keys = array_keys($configs);
			$currentKey = array_search($fieldId, $keys);
			$swap = (isset($_POST["move_up"])) ? $currentKey - 1 :$currentKey + 1;

			if($swap >= 0 && $swap < count($keys)){
				$tmp = $keys[$currentKey];
				$keys[$currentKey] = $keys[$swap];
				$keys[$swap] = $tmp;

				$tmpArray = array();
				foreach($keys as $index => $id){
					$field = $configs[$id];
					$tmpArray[$id] = $field;
				}

				AddMailTypeUtil::saveConfig($tmpArray);

				$this->configObj->redirect("updated");
			}
		}
	}

	function execute(){
		parent::__construct();

		DisplayPlugin::toggle("created", isset($_GET["created"]));
		DisplayPlugin::toggle("removed", isset($_GET["removed"]));
		DisplayPlugin::toggle("failed", isset($_GET["failed"]));

		$this->addForm("form");

		$orderCnfs = AddMailTypeUtil::getConfig();
		$userCnfs = AddMailTypeUtil::getConfig(AddMailTypeUtil::MAIL_TYPE_USER);

		DisplayPlugin::toggle("no_config", (!count($orderCnfs) && !count($userCnfs)));

		DisplayPlugin::toggle("order_mail", (is_array($orderCnfs) && count($orderCnfs)));
		$this->createAdd("mail_type_list", "MailTypeListComponent", array(
			"list" => $orderCnfs,
			"mode" => AddMailTypeUtil::MAIL_TYPE_ORDER
		));

		DisplayPlugin::toggle("user_mail", (is_array($userCnfs) && count($userCnfs)));
		$this->createAdd("user_mail_type_list", "MailTypeListComponent", array(
			"list" => $userCnfs,
			"mode" => AddMailTypeUtil::MAIL_TYPE_USER
		));

		$this->addForm("create_form");

		$this->addInput("mail_title", array(
			"name" => "mail_title",
			"value" => "",
			"required" => "required"
		));

		$this->addInput("mail_id", array(
			"name" => "mail_id",
			"value" => "",
			"required" => "required",
			"pattern" => "^[a-zA-Z0-9]+$"
		));

		$this->addCheckBox("mail_type_order", array(
			"name" => "mail_type",
			"value" => AddMailTypeUtil::MAIL_TYPE_ORDER,
			"selected" => true,
			"label" => "注文関連メール"
		));

		$this->addCheckBox("mail_type_user", array(
			"name" => "mail_type",
			"value" => AddMailTypeUtil::MAIL_TYPE_USER,
			"label" => "顧客詳細メール"
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
