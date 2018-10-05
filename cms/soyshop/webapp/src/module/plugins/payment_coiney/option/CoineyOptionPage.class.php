<?php

class CoineyOptionPage extends WebPage{

	private $order;
	private $cart;

	function __construct(){
		SOY2::import("module.plugins.payment_coiney.util.CoineyUtil");
	}

	function doPost(){}

	function execute(){
		parent::__construct();

		$json = SOY2Logic::createInstance("module.plugins.payment_coiney.logic.CoineyApiLogic")->createPaymentRequest($this->order);

		DisplayPlugin::toggle("error", !isset($json["id"]));
		DisplayPlugin::toggle("success", isset($json["id"]));

		$this->addLabel("error_message", array(
			"text" => (isset($json["code"])) ? CoineyUtil::getErrorText($json["code"]) : ""
		));

		if(isset($json["id"])){
			//支払いIDを記録しておく
			$this->cart->setAttribute("coiney_id", $json["id"]);
		}

		$this->addForm("payment_form", array(
			"method" => "GET",
			"action" => (isset($json["links"]["paymentUrl"])) ? $json["links"]["paymentUrl"] : ""
		));

		// $this->addLink("payment_url", array(
		// 	"link" => (isset($json["links"]["paymentUrl"])) ? $json["links"]["paymentUrl"] : ""
		// ));

		$this->addLink("cancel_link", array(
			"link" => soyshop_get_cart_url(false, true) . "?cancel=1"
		));
	}

	function setOrder($order) {
		$this->order = $order;
	}
	function setCart($cart){
		$this->cart = $cart;
	}
}
