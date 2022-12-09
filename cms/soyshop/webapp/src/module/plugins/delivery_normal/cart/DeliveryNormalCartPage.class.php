<?php

class DeliveryNormalCartPage extends WebPage{

	private $cart;
	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.delivery_normal.util.DeliveryNormalUtil");
		SOY2::import("util.SOYShopPluginUtil");
	}

	function execute(){
		parent::__construct();

		$this->addLabel("module_description", array(
			"html" => DeliveryNormalUtil::getDescription()
		));

		$useDeliveryTime = DeliveryNormalUtil::getUseDeliveryTimeConfig();

		//配達時間帯の指定を利用するか？
		DisplayPlugin::toggle("display_delivery_time_table", (isset($useDeliveryTime["use"]) && $useDeliveryTime["use"] == 1));

		$this->addSelect("delivery_time", array(
			"name" => "delivery_time",
			"options" => DeliveryNormalUtil::getDeliveryTimeConfig(),
			"selected" => $this->cart->getOrderAttribute("delivery_normal.time")
		));

		//お届け日の指定を利用するか？
		$config = DeliveryNormalUtil::getDeliveryDateConfig();
		DisplayPlugin::toggle("display_delivery_date_table", (isset($config["use_delivery_date"]) && $config["use_delivery_date"] == 1));

		//カレンダー形式
		DisplayPlugin::toggle("display_format_calendar", (isset($config["use_format_calendar"]) && $config["use_format_calendar"] == 1));

		$dateStr = (isset($this->cart->getOrderAttribute("delivery_normal.date")["value"])) ? $this->cart->getOrderAttribute("delivery_normal.date")["value"] : "";
		if(strpos($dateStr, "指定") !== false) $dateStr = null;

		$this->addInput("delivery_date_calendar", array(
			"name" => "delivery_date",
			"value" => $dateStr,
			"id" => "jquery-ui-calendar",
			"style" => "width:120px",
			"readonly" => true
		));

		//セレクトボックス形式
		DisplayPlugin::toggle("display_format_select", (!isset($config["use_format_calendar"]) || $config["use_format_calendar"] != 1));
		$this->addSelect("delivery_date", array(
			"name" => "delivery_date",
			"options" =>  DeliveryNormalUtil::getDeliveryDateOptions($config),
			"selected" => $this->cart->getOrderAttribute("delivery_normal.date")
		));
	}

	private function getDateLogic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("module.plugins.delivery_normal.logic.DeliveryDateFormatLogic");
		return $logic;
	}

	function setCart($cart){
		$this->cart = $cart;
	}
	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
