<?php
/*
 *
 */
class DeliveryCountFreeCartFormPage extends WebPage{
	private $cart;
	
	function DeliveryCountFreeConfigFormPage(){
	}
	
	function execute(){
		WebPage::WebPage();

		$this->createAdd("description","HTMLLabel", array(
			"html"  => nl2br(DeliveryCountFreeConfigUtil::getDescription()),
		));

		$attr = ($this->cart) ? $this->cart->getOrderAttributes() : null;
		$this->createAdd("delivery_count_free_date","HTMLInput", array(
			"name"  => "delivery_count_free_date",
			"value" => @$attr["delivery_count_free.date"]["value"]
		));
		
		$this->createAdd("delivery_count_free_time","HTMLSelect", array(
			"name"       => "delivery_count_free_time",
			"options"    => DeliveryCountFreeConfigUtil::getDliveryTimeConfig(),
			"selected"   => @$attr["delivery_count_free.time"]["value"]
		));
		
	}
	
	function getTemplateFilePath(){
		return dirname(__FILE__) . "/cart.html";
	}
	
	function setCart($cart){
		$this->cart = $cart;
	}

}
