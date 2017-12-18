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
			"modules" => $this->cart->getModules(),
			"include" => false
		));

		$items = PaymentConstructionUtil::getIncludeItemList();
		$this->createAdd("include_list", "CommissionListComponent", array(
			"list" => $items,
			"modules" => $this->cart->getModules(),
			"include" => true
		));

		DisplayPlugin::toggle("has_include_item", ($items) > 0);

		DisplayPlugin::toggle("construction_item", PaymentConstructionUtil::hasConstructionItem());
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
