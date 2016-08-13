<?php

class DeliveryCan7CartPage extends WebPage{
	
	private $cart;
	
	function __construct(){
		SOY2::import("module.plugins.delivery_can7.util.DeliveryCan7Util");
	}
	
	function execute(){
		WebPage::__construct();

		$this->addLabel("description", array(
			"html"  => nl2br(DeliveryCan7Util::getDescription()),
		));

		$attr = ($this->cart) ? $this->cart->getOrderAttributes() : null;
		$this->addInput("delivery_can7_date", array(
			"name"  => "delivery_can7_date",
			"value" => @$attr["delivery_count_free.date"]["value"]
		));
		
		$this->addSelect("delivery_can7_time", array(
			"name"       => "delivery_count_free_time",
			"options"    => DeliveryCan7Util::getDliveryTimeConfig(),
			"selected"   => @$attr["delivery_count_free.time"]["value"]
		));
	}
		
	function setCart($cart){
		$this->cart = $cart;
	}
}
?>