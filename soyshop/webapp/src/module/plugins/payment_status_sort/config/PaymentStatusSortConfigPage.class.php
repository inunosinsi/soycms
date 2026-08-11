<?php

class PaymentStatusSortConfigPage extends WebPage {

	private $configObj;

	function __construct(){
		SOY2::import("domain.order.SOYShop_Order");
		SOY2::import("module.plugins.payment_status_sort.component.PaymentStatusListComponent");
		SOY2::import("module.plugins.payment_status_sort.util.PaymentStatusSortUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			PaymentStatusSortUtil::saveConfig($_POST["Sort"]);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$this->addForm("form");

		$order = new SOYShop_Order();
		$this->createAdd("order_status_list", "PaymentStatusListComponent", array(
			"list" => $order->getPaymentStatusList(),
			"config" => PaymentStatusSortUtil::getConfig()
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
