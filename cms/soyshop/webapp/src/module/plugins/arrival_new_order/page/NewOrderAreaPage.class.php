<?php

class NewOrderAreaPage extends WebPage{
	
	private $configObj;
	
	function __construct(){}
	
	function execute(){
		WebPage::__construct();
		
		$orderDao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
		$orderDao->setLimit(16);
		try{
			$orders = $orderDao->getByStatus(SOYShop_Order::ORDER_STATUS_REGISTERED);
		}catch(Exception $e){
			$orders = array();
		}

		DisplayPlugin::toggle("more_order", (count($orders) > 15));
		DisplayPlugin::toggle("has_order", (count($orders) > 0));
		DisplayPlugin::toggle("no_order", (count($orders) === 0));

		$orders = array_slice($orders, 0, 15);

		$this->createAdd("order_list", "_common.Order.OrderListComponent", array(
			"list" => $orders
		));
	}
	
	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
?>