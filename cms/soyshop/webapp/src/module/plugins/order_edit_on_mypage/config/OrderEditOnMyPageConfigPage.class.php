<?php

class OrderEditOnMyPageConfigPage extends WebPage {

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.order_edit_on_mypage.util.OrderEditOnMyPageUtil");
		SOY2::import("module.plugins.arrival_new_order.fn.fn", ".php");
	}

	function doPost(){
		if(soy2_check_token()){
			OrderEditOnMyPageUtil::saveMailConfig($_POST["Mail"]);
			soyshop_save_arrival_config("order_edit_on_mypage", $_POST["Arrival"]);
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

		$cfgs = soyshop_get_arrival_config("order_edit_on_mypage");
		
		$this->addInput("arrival_count", array(
			"name" => "Arrival[count]",
			"value" => (isset($cfgs["count"]) && is_numeric($cfgs["count"])) ? $cfgs["count"] : 15
		));

		$this->addInput("arrival_period", array(
			"name" => "Arrival[period]",
			"value" => (isset($cfgs["period"]) && is_numeric($cfgs["period"])) ? $cfgs["period"] : 12
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
