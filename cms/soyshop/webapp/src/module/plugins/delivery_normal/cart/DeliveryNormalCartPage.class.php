<?php

class DeliveryNormalCartPage extends WebPage{
	
	private $cart;
	private $configObj;
	
	function DeliveryNormalCartPage(){
		SOY2::import("module.plugins.delivery_normal.util.DeliveryNormalUtil");
	}
	
	function execute(){
		WebPage::WebPage();
		
		$this->addLabel("module_description", array(
			"text" => DeliveryNormalUtil::getDescription()
		));
		
		$useDeliveryTime = DeliveryNormalUtil::getUseDeliveryTimeConfig();
		
		//配達時間帯の指定を利用するか？
		$this->addModel("display_delivery_time_table", array(
			"visible" => (isset($useDeliveryTime["use"]) && $useDeliveryTime["use"] == 1)
		));
		
		$this->addSelect("delivery_time", array(
			"name" => "delivery_time",
			"options" => DeliveryNormalUtil::getDeliveryTimeConfig(),
			"selected" => $this->cart->getOrderAttribute("delivery_normal.time")
		));
	}
	
	function setCart($cart){
		$this->cart = $cart;
	}
	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
?>