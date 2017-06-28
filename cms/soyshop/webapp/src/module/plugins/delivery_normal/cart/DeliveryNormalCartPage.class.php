<?php

class DeliveryNormalCartPage extends WebPage{

	private $cart;
	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.delivery_normal.util.DeliveryNormalUtil");
		SOY2::import("util.SOYShopPluginUtil");
	}

	function execute(){
		WebPage::__construct();

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

		$this->addSelect("delivery_date", array(
			"name" => "delivery_date",
			"options" => self::getDeliveryDateOptions($config),
			"selected" => $this->cart->getOrderAttribute("delivery_normal.date")
		));
	}

	private function getDeliveryDateOptions($config){

		//最短の日付を取得
		$time = time();

		//営業日を加味
		if(
			isset($config["use_re_calc_shortest_date"]) &&
			$config["use_re_calc_shortest_date"] == 1 &&
			SOYShopPluginUtil::checkIsActive("parts_calendar")
		){
			$time = SOY2Logic::createInstance("module.plugins.parts_calendar.logic.BusinessDateLogic")->getNextBusinessDate();
		}

		$shortest = $time + (int)$config["delivery_shortest_date"] * 24 * 60 * 60;
		$last = $shortest + (int)$config["delivery_date_period"] * 24 * 60 * 60;

		$logic = SOY2Logic::createInstance("module.plugins.delivery_normal.logic.DeliveryDateFormatLogic");

		$opts = array();

		//指定なしの項目を追加
		if(isset($config["use_delivery_date_unspecified"]) && $config["use_delivery_date_unspecified"] == 1){
			$opts[] = "指定なし";
		}

		do{
			$opts[date("Y-m-d", $shortest)] = $logic->convertDateString($config["delivery_date_format"], $shortest);
			$shortest += 24 * 60 * 60;
		}while($shortest < $last);


		return $opts;
	}

	function setCart($cart){
		$this->cart = $cart;
	}
	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
