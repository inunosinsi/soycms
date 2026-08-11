<?php

class SOYMailConnectorConfigFormPage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.soymail_connector.util.SOYMailConnectorUtil");
	}

	function doPost(){

		if(soy2_check_token() && isset($_POST["Config"])){
			SOYMailConnectorUtil::saveConfig($_POST["Config"]);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$config = SOYMailConnectorUtil::getConfig();

		$this->addForm("form");

		$this->addCheckBox("is_check", array(
			"name" => "Config[isCheck]",
			"value" => SOYMailConnectorUtil::SEND,
			"selected" => (isset($config["isCheck"]) && (int)$config["isCheck"] === SOYMailConnectorUtil::SEND),
			"label" => "メルマガ希望のチェックボックスで最初からチェックしておく(カートページのみ)"
		));

		$this->addInput("first_order_add_point", array(
			"name" => "Config[first_order_add_point]",
			"value" => (isset($config["first_order_add_point"])) ? (int)$config["first_order_add_point"] : 0,
			"style" => "width:70px;text-align:right;"
		));

		DisplayPlugin::toggle("no_active_point_plugin", (!class_exists("SOYShopPluginUtil") || !SOYShopPluginUtil::checkIsActive("common_point_base")));

		$this->addInput("first_order_add_point_description", array(
			"name" => "Config[first_order_add_point_text]",
			"value" => (isset($config["first_order_add_point_text"])) ? $config["first_order_add_point_text"] : "",
			"style" => "width:80%;"
		));

		$this->addInput("label", array(
			"name" => "Config[label]",
			"value" => (isset($config["label"])) ? $config["label"] : ""
		));

		$this->addInput("description", array(
			"name" => "Config[description]",
			"value" => (isset($config["description"])) ? $config["description"] : ""
		));

		$this->addCheckBox("insert_mail", array(
			"name" => "Config[isInsertMail]",
			"value" => SOYMailConnectorUtil::INSERT,
			"selected" => (isset($config["isInsertMail"]) && $config["isInsertMail"] == SOYMailConnectorUtil::INSERT),
			"label" => "注文時のメール文面にメールマガジン配信の有無を挿入する"
		));

		/** 説明文用 **/
		$mypageDirectory = soy2_realpath(SOY2::RootDir()) . "mypage/" . soyshop_get_mypage_id() . "/pages/";
		$this->addLabel("register_path", array(
			"text" => $mypageDirectory . "register/IndexPage.html"
		));
		$this->addLabel("edit_path", array(
			"text" => $mypageDirectory . "edit/IndexPage.html"
		));
		$this->addLabel("register_confirm_path", array(
			"text" => $mypageDirectory . "register/ConfirmPage.html"
		));
		$this->addLabel("edit_confirm_path", array(
			"text" => $mypageDirectory . "edit/ConfirmPage.html"
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
?>
