<?php

class DeliveryClickPostCartPage extends WebPage{

	private $cart;

	function __construct(){
		SOY2::import("module.plugins.delivery_clickpost.util.ClickPostUtil");
	}

	function execute(){
		parent::__construct();

		$cfg = ClickPostUtil::getConfig();
		$this->addLabel("module_description", array(
			"html" => (isset($cfg["description"])) ? nl2br(htmlspecialchars($cfg["description"], ENT_QUOTES, "UTF-8")) : ""
		));
	}

	function setCart(CartLogic $cart){
		$this->cart = $cart;
	}
}
