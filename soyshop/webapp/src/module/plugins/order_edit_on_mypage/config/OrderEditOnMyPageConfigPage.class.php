<?php

class OrderEditOnMyPageConfigPage extends WebPage {

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.order_edit_on_mypage.util.OrderEditOnMyPageUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			OrderEditOnMyPageUtil::saveMailConfig($_POST["Mail"]);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		self::buildForm();
	}

	private function buildForm(){

		$mailConfig = OrderEditOnMyPageUtil::getMailConfig();

		$this->addForm("form");

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
