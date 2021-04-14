<?php

class NewOrderAreaPage extends WebPage{

	private $configObj;

	function __construct(){}

	function execute(){
		parent::__construct();

		$orderDao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
		$orderDao->setLimit(16);
		try{
			$orders = $orderDao->getByStatus(SOYShop_Order::ORDER_STATUS_REGISTERED);
		}catch(Exception $e){
			$orders = array();
		}

		$cnt = count($orders);
		DisplayPlugin::toggle("more_order", $cnt > 15);
		DisplayPlugin::toggle("has_order", $cnt > 0);
		DisplayPlugin::toggle("no_order", $cnt === 0);

		if($cnt > 15) $orders = array_slice($orders, 0, 15);

		$this->createAdd("order_list", "_common.Order.OrderListComponent", array(
			"list" => $orders,
			"userNameList" => SOY2Logic::createInstance("logic.user.UserLogic")->getUserNameListByUserIds(soyshop_get_user_ids_by_orders($orders))
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
