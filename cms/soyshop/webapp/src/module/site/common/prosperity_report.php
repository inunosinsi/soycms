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
			"userDao" => SOY2DAOFactory::create("user.SOYShop_UserDAO"),
			"itemDao" => SOY2DAOFactory::create("shop.SOYShop_ItemDAO"),
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
	private $itemDao;
	
	protected function populateItem($entity, $key, $int){
		$user = self::getUserById($entity->getUserId());
		
		$this->addLabel("number", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => $int
		));
		
		$this->createAdd("report_order_date", "DateLabel", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,			
			"text" => $entity->getOrderDate(),
			"defaultFormat" => "Y.m.d"
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
		
		$values = self::getItemsByOrderId($entity->getId());
		
		//価格が一番高い商品
		$this->addLink("price_max_item_link", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"link" => soyshop_get_item_detail_link($values["price"])
		));
		
		$this->addLabel("price_max_item_name", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => $values["price"]->getName()
		));
		
		//購入数が一番多い商品
		$this->addLink("count_max_item_link", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"link" => soyshop_get_item_detail_link($values["count"])
		));
		
		$this->addLabel("count_max_item_name", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => $values["count"]->getName()
		));
		
		if(is_null($values["price"]->getId())) return false;
	}
	
	private function getUserById($userId){
		try{
			return $this->userDao->getById($userId);
		}catch(Exception $e){
			return new SOYShop_User();
		}
	}
	
	private function getItemsByOrderId($orderId){
		$sql = "SELECT item.*, os.item_count AS COUNT, os.item_price AS PRICE FROM soyshop_item item ".
				"INNER JOIN soyshop_orders os ".
				"ON item.id = os.item_id ".
				"WHERE os.order_id = :orderId ".
				"AND item.item_is_open != 0 ".
				"AND item.is_disabled != 1 ".
				"AND item.open_period_start < " . time() . " ".
				"AND item.open_period_end > " . time();
				
		try{
			$res = $this->itemDao->executeQuery($sql, array(":orderId" => $orderId));
		}catch(Exception $e){
			$res = array();
		}
		
		if(!count($res)) return array("price" => new SOYShop_Item(), "count" => new SOYShop_Item());
		
		$maxValue = 0;
		$maxCnt = 0;
		
		//最高値の商品が格納される
		$valueTop = 0;
		$cntTop = 0;
		
		foreach($res as $key => $vals){
			if((int)$vals["PRICE"] > $maxValue){
				$maxValue = (int)$vals["PRICE"];
				$valueTop = $key;
			}
			
			if((int)$vals["COUNT"] > $maxCnt){
				$maxCnt = (int)$vals["COUNT"];
				$cntTop = $key;
			}
		}
		
		//SOYShop_Itemに変換
		$array["price"] = $this->itemDao->getObject($res[$valueTop]);
		$array["count"] = $this->itemDao->getObject($res[$cntTop]);
		
		return $array;
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
	
	function setItemDao($itemDao){
		$this->itemDao = $itemDao;
	}
}
?>