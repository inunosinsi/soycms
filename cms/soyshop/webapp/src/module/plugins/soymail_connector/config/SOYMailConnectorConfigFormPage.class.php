<?php

class SOYMailConnectorConfigFormPage extends WebPage{
	
	private $configObj;
	
	function SOYMailConnectorConfigFormPage(){
		SOY2::import("module.plugins.soymail_connector.util.SOYMailConnectorUtil");
	}
	
	function doPost(){
		
		if(soy2_check_token() && isset($_POST["Config"])){
			SOYMailConnectorUtil::saveConfig($_POST["Config"]);
			$this->configObj->redirect("updated");
		}
	}
	
	function execute(){
		WebPage::WebPage();
		
		$config = SOYMailConnectorUtil::getConfig();
		
		$this->addModel("updated", array(
			"visible" => (isset($_GET["updated"]))
		));
		
		$this->addForm("form");
		
		$this->addCheckBox("is_check", array(
			"name" => "Config[isCheck]",
			"value" => SOYMailConnectorUtil::SEND,
			"selected" => (isset($config["isCheck"]) && (int)$config["isCheck"] === SOYMailConnectorUtil::SEND),
			"label" => "メルマガ希望のチェックボックスで最初からチェックしておく(カートページのみ)"
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