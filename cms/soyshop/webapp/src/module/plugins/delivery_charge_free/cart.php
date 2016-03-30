<?php
/*
 *
 */
class DeliveryChargeFreeCartFormPage extends WebPage{
	private $cart;
	
	function DeliveryChargeFreeConfigFormPage(){
	}
	
	function execute(){
		WebPage::WebPage();

		$this->addLabel("description", array(
			"html"  => nl2br(DeliveryChargeFreeConfigUtil::getDescription()),
		));

		$attr = ($this->cart) ? $this->cart->getOrderAttributes() : null;
		$this->addInput("delivery_charge_free_date", array(
			"name"  => "delivery_charge_free_date",
			"value" => (isset($attr["delivery_charge_free.date"]["value"])) ? $attr["delivery_charge_free.date"]["value"] : ""
		));
		
		$this->addSelect("delivery_charge_free_time", array(
			"name"       => "delivery_charge_free_time",
			"options"    => DeliveryChargeFreeConfigUtil::getDliveryTimeConfig(),
			"selected"   => (isset($attr["delivery_charge_free.time"]["value"])) ? $attr["delivery_charge_free.time"]["value"] : ""
		));
		
	}
	
	function getTemplateFilePath(){
		return dirname(__FILE__) . "/cart.html";
	}
	
	function setCart($cart){
		$this->cart = $cart;
	}

}
