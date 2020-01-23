<?php

class InquiryConfigPage extends WebPage {

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.inquiry_on_mypage.util.InquiryOnMypageUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			InquiryOnMypageUtil::saveConfig($_POST["Config"]);
			InquiryOnMypageUtil::saveMailConfig($_POST["Mail"]);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		self::buildForm();
	}

	private function buildForm(){
		$config = InquiryOnMypageUtil::getConfig();
		$mailConfig = InquiryOnMypageUtil::getMailConfig();

		$this->addForm("form");

		$this->addCheckBox("add_tab", array(
			"name" => "Config[tab]",
			"value" => 1,
			"selected" => (isset($config["tab"]) && $config["tab"] == 1),
			"label" => "管理画面のナビに『お問い合せ』ボタンを追加する"
		));

		$this->addTextArea("requirement", array(
			"name" => "Config[requirement]",
			"value" => (isset($config["requirement"])) ? $config["requirement"] : "",
			"style" => "height:100px;"
		));

		$this->addInput("mail_title", array(
			"name" => "Mail[title]",
			"value" => $mailConfig["title"]
		));

		$this->addTextArea("mail_header", array(
			"name" => "Mail[header]",
			"value" => $mailConfig["header"]
		));

		$this->addTextArea("mail_footer", array(
			"name" => "Mail[footer]",
			"value" => $mailConfig["footer"]
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
