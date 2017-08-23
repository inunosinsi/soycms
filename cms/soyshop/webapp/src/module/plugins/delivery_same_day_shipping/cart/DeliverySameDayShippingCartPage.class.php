<?php

class DeliverySameDayShippingCartPage extends WebPage{
	
	private $cart;
	private $configObj;
	
	private $pluginConfig;
	
	function __construct(){}
	
	function execute(){
		parent::__construct();
		
		$logic = SOY2Logic::createInstance("module.plugins.delivery_same_day_shipping.logic.ShippingDateLogic", array("config" => $this->pluginConfig));
		$values = $logic->get();

		$arrivalDate = $values[1];
		$description = $values[2];
		
		//発送予定日の配列
		$bArray = explode("-", date("Y-n-j", $values[0]));
		
		//到着予定日の配列
		$aArray = explode("-", date("Y-n-j", $values[1]));
				
		//説明文
		$this->addLabel("content", array(
			"html" => nl2br($logic->convertDescription($values))
		));
		
		//発送予定日
		$this->addInput("expected_shipping_date", array(
			"type" => "hidden",
			"name" => "DeliveryPlugin[shippingDate]",
			"value" => $bArray[0] . "-" . $bArray[1] . "-" . $bArray[2]
		));
		
		//到着予定日
		$this->addInput("scheduled_arrival_date", array(
			"type" => "hidden",
			"name" => "DeliveryPlugin[arrivalDate]",
			"value" => $aArray[0] . "-" . $aArray[1] . "-" . $aArray[2]
		));
		
		unset($values);
	}
	
	function setCart($cart){
		$this->cart = $cart;
	}
	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
	function setPluginConfig($pluginConfig){
		$this->pluginConfig = $pluginConfig;
	}
}
?>