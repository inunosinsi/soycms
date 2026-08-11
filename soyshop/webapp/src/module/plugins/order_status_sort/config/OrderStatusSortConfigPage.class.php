<?php

class OrderStatusSortConfigPage extends WebPage {

	private $configObj;

	function __construct(){
		SOY2::import("domain.order.SOYShop_Order");
		SOY2::import("module.plugins.order_status_sort.component.OrderStatusListComponent");
		SOY2::import("module.plugins.order_status_sort.util.OrderStatusSortUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			OrderStatusSortUtil::saveConfig($_POST["Sort"]);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$this->addForm("form");

		$order = new SOYShop_Order();
		$this->createAdd("order_status_list", "OrderStatusListComponent", array(
			"list" => $order->getOrderStatusList(false),
			"config" => OrderStatusSortUtil::getConfig()
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
