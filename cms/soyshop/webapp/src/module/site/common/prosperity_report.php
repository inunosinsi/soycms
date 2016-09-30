<?php

function soyshop_prosperity_report($html, $htmlObj){


	$obj = $htmlObj->create("soyshop_prosperity_report", "HTMLTemplatePage", array(
		"arguments" => array("soyshop_prosperity_report", $html)
	));
	
	$orders = array();
	
	SOY2::import("util.SOYShopPluginUtil");
	if(SOYShopPluginUtil::checkIsActive("prosperity_report")){
		
		$lim = 10;
		if(preg_match('/cms:count=\"(.*)\"/', $html, $tmp)){
			if(isset($tmp[1]) && is_numeric($tmp[1])) $lim = (int)$tmp[1];
		}

		$orderDao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
		$sql = "SELECT * FROM soyshop_order ".
				"WHERE order_status > " . SOYShop_Order::ORDER_STATUS_INTERIM . " ".
				"AND order_status < " . SOYShop_Order::ORDER_STATUS_CANCELED . " ".
				"ORDER BY order_date DESC ".
				"LIMIT ". $lim;
				
		try{
			$results = $orderDao->executeQuery($sql);
		}catch(Exception $e){
			$results = array();
		}
		
		foreach($results as $res){
			if((int)$res["id"] > 0){
				$orders[] = $orderDao->getObject($res);
			}
		}
		
		SOY2::import("domain.config.SOYShop_Area");
		$obj->createAdd("prosperity_order_list", "ProsperityReportOrderListComponent", array(
			"soy2prefix" => "block",
			"list" => $orders,
			"userDao" => SOY2DAOFactory::create("user.SOYShop_UserDAO")
		));
	}

	//商品があるときだけ表示
	if(count($orders) > 0){
		$obj->display();
	}else{
		ob_start();
		$obj->display();
		ob_end_clean();
	}
}

class ProsperityReportOrderListComponent extends HTMLList{
	
	private $userDao;
	
	protected function populateItem($entity, $key, $int){
		$user = self::getUserById($entity->getUserId());
		
		$this->addLabel("number", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => $int
		));
		
		$this->addLabel("customer_pref", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => SOYShop_Area::getAreaText($user->getArea())
		));
		
		$this->addLabel("tracking_number", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => $entity->getTrackingNumber()
		));
		
		$this->addLabel("order_total_price", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => number_format($entity->getPrice())
		));
		
		$this->addLabel("item_total_price", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => number_format(self::getItemTotalPrice($entity->getId()))
		));
	}
	
	private function getUserById($userId){
		try{
			return $this->userDao->getById($userId);
		}catch(Exception $e){
			return new SOYShop_User();
		}
	}
	
	private function getItemTotalPrice($orderId){
		$sql = "SELECT SUM(total_price) AS TOTAL FROM soyshop_orders ".
				"WHERE order_id = :orderId";
				
		try{
			$results = $this->userDao->executeQuery($sql, array(":orderId" => $orderId));
		}catch(Exception $e){
			$results = array();
		}
		
		return (isset($results[0]["TOTAL"])) ? (int)$results[0]["TOTAL"] : 0;
	}
	
	function setUserDao($userDao){
		$this->userDao = $userDao;
	}
}
?>