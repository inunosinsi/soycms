<?php

class NewOrderAreaPage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.arrival_new_order.fn.fn", ".php");
	}

	function execute(){
		parent::__construct();

		$orders = self::_get();

		$cnt = count($orders);
		
		$cfgs = soyshop_get_arrival_config("arrival_new_order");
		DisplayPlugin::toggle("more_order", $cnt > $cfgs["count"]);
		DisplayPlugin::toggle("has_order", $cnt > 0);
		DisplayPlugin::toggle("no_order", $cnt === 0);

		if($cnt > $cfgs["count"]) $orders = array_slice($orders, 0, $cfgs["count"]);

		$this->addLabel("display_count", array(
			"text" => $cfgs["count"]	
		));

		$this->createAdd("order_list", "_common.Order.OrderListComponent", array(
			"list" => $orders,
			"userNameList" => ($cnt > 0 ) ? SOY2Logic::createInstance("logic.user.UserLogic")->getUserNameListByUserIds(soyshop_get_user_ids_by_orders($orders)) : array()
		));
	}

	private function _get(){
		$cfgs = soyshop_get_arrival_config("arrival_new_order");
	
		$dao = soyshop_get_hash_table_dao("order");
		try{
			$res = $dao->executeQuery(
				"SELECT id, order_date, price, payment_status, user_id, tracking_number FROM soyshop_order ".
				"WHERE order_status = :status ".
				"AND order_date > :date ".
				"ORDER BY order_date DESC ".
				"LIMIT ".($cfgs["count"]+1),
				array(
					":status" => SOYShop_Order::ORDER_STATUS_REGISTERED,
					":date" => strtotime("-".$cfgs["period"]."month")
				)
			);
		}catch(Exception $e){
			return array();
		}

		if(!count($res)) return array();

		$_arr = array();
		foreach($res as $v){
			$_arr[] = $dao->getObject($v);
		}
		return $_arr;
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
