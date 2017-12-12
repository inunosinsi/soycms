<?php

class CommissionPage extends WebPage{

	private $configObj;
	private $cart;

	function __construct(){
		SOY2::import("module.plugins.payment_construction.util.PaymentConstructionUtil");
		SOY2::import("module.plugins.payment_construction.component.CommissionListComponent");
	}

	function execute(){
		parent::__construct();

		$this->createAdd("commission_list", "CommissionListComponent", array(
			"list" => PaymentConstructionUtil::getCommissionItemList(),
			"modules" => $this->cart->getModules()
		));

		$attrs = $this->cart->getAttributes();
		$this->addInput("construction_fee", array(
			"name" => "construction_fee",
			"value" => (isset($attrs["payment_construction_fee"])) ? (int)$attrs["payment_construction_fee"] : 0
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}

	function setCart($cart){
		$this->cart = $cart;
	}
}
