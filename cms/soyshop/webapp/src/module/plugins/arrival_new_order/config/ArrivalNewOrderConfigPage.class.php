<?php

class ArrivalNewOrderConfigPage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2::import("domain.order.SOYShop_Order");
		SOY2::import("module.plugins.arrival_new_order.util.ArrivalNewOrderUtil");
		SOY2::import("module.plugins.arrival_new_order.fn.fn", ".php");
	}

	function doPost(){
		if(soy2_check_token()){
			$values = (isset($_POST["Config"]) && is_array($_POST["Config"])) ? $_POST["Config"] : array();
			ArrivalNewOrderUtil::saveConfig($values);
			soyshop_save_arrival_config("arrival_new_order", $_POST["Arrival"]);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$config = ArrivalNewOrderUtil::getConfig();

		$this->addForm("form");

		$this->addCheckBox("error_wait", array(
			"name" => "Config[error][".SOYShop_Order::PAYMENT_STATUS_WAIT."]",
			"value" => ArrivalNewOrderUtil::ON,
			"selected" => (isset($config["error"][SOYShop_Order::PAYMENT_STATUS_WAIT]) && (int)$config["error"][SOYShop_Order::PAYMENT_STATUS_WAIT] === ArrivalNewOrderUtil::ON),
			"label" => "仮注文で支払い待ちの注文を表示する"
		));

		$this->addCheckBox("error_confirm", array(
			"name" => "Config[error][".SOYShop_Order::PAYMENT_STATUS_CONFIRMED."]",
			"value" => ArrivalNewOrderUtil::ON,
			"selected" => (isset($config["error"][SOYShop_Order::PAYMENT_STATUS_CONFIRMED]) && (int)$config["error"][SOYShop_Order::PAYMENT_STATUS_CONFIRMED] === ArrivalNewOrderUtil::ON),
			"label" => "仮注文で支払確認済みの注文を表示する"
		));

		$cfgs = soyshop_get_arrival_config("arrival_new_order");

		$this->addInput("arrival_count", array(
			"name" => "Arrival[count]",
			"value" => (isset($cfgs["count"]) && is_numeric($cfgs["count"])) ? $cfgs["count"] : 15
		));

		$this->addInput("arrival_period", array(
			"name" => "Arrival[period]",
			"value" => (isset($cfgs["period"]) && is_numeric($cfgs["period"])) ? $cfgs["period"] : 12
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
