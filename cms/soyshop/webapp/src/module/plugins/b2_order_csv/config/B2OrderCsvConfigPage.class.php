<?php

class B2OrderCsvConfigPage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.b2_order_csv.util.B2OrderCsvUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			B2OrderCsvUtil::saveConfig($_POST["config"]);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$this->addForm("form");

		$config = B2OrderCsvUtil::getConfig();

		$this->createAdd("b2_customer_number","HTMLInput", array(
			"name" => "config[number]",
			"value" => (isset($config["number"])) ? $config["number"] : ""
		));

		$this->addInput("b2_item_name", array(
			"name" => "config[name]",
			"value" => (isset($config["name"])) ? $config["name"] : ""
		));

		$this->addCheckBox("b2_auto_insert_shipping_date", array(
			"name" => "config[auto_insert_shipping_date]",
			"value" => 1,
			"selected" => (isset($config["auto_insert_shipping_date"]) && $config["auto_insert_shipping_date"] == 1),
			"label" => "出荷予定日のカラムに出力時の日付を挿入する"
		));

		$this->addCheckBox("b2_neko_pos", array(
			"name" => "config[neko_pos]",
			"value" => 1,
			"selected" => (isset($config["neko_pos"]) && $config["neko_pos"] == 1),
			"label" => "ネコポスを利用する"
		));

		$this->addInput("b2_customer_code", array(
			"name" => "config[customer_code]",
			"value" => (isset($config["customer_code"])) ? $config["customer_code"] : ""
		));
	}

	function setConfigObj($configObj) {
		$this->configObj = $configObj;
	}
}
