<?php

class Analytics_AreaPage extends Analytics_CommonPage{
	
	private $userDao;
	
	function build_print(){
		
		$this->userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
		
		//共通部分の表示
		$this->buildCommon();
		
		$start = AnalyticsPluginUtil::convertTitmeStamp("start");
		$end = AnalyticsPluginUtil::convertTitmeStamp("end");
		
		SOY2::import("domain.config.SOYShop_Area");
		SOY2::import("domain.order.SOYShop_Order");
		$this->createAdd("area_graph_list", "AreaGraphListComponent", array(
			"list" => $this->calc($start, $end)
		));
	}
	
	function calc($start, $end){
		
		try{
			$results = $this->userDao->executeQuery($this->buildSql(), array(":start" => $start, ":end" => $end));
		}catch(Exception $e){
			return array();
		}
		
		for($i = 0; $i < count($results); $i++){
			$r = rand(0,255);
			$g = rand(0,255);
			$b = rand(0,255);
			$a = "0.".rand(1,9);
			$results[$i]["color"] = "rgba(" . $r . ", " . $g . ", " . $b . ", " . $a . ")";
		}
		
		return $results;
	}
	
	function buildSql(){
		$sql = "SELECT area, COUNT(*) occurrences ".
				"FROM soyshop_user ".
				"WHERE id IN (" .
					"SELECT DISTINCT user.id " .
					"FROM soyshop_user user ".
					"JOIN soyshop_order o ".
					"ON user.id = o.user_id ".
					"WHERE o.order_status > " . SOYShop_Order::ORDER_STATUS_INTERIM . " ".
					"AND o.order_status < " . SOYShop_Order::ORDER_STATUS_CANCELED . " ".
					"AND o.order_date >= :start ".
					"AND o.order_date <= :end " .
					"AND user.is_disabled != " . SOYShop_User::USER_IS_DISABLED . " ".
				")".
				"GROUP BY area ".
				"HAVING area > 0 ".
				"ORDER BY occurrences DESC";
		
		if(isset($_POST["AnalyticsPlugin"]["limit"]) && strlen($_POST["AnalyticsPlugin"]["limit"]) > 0 && is_numeric($_POST["AnalyticsPlugin"]["limit"])){
			$sql .= " LIMIT " . (int)$_POST["AnalyticsPlugin"]["limit"];
		}
		
		return $sql;
	}
}

class AreaGraphListComponent extends HTMLList{
	
	protected function populateItem($entity) {
		$this->addLabel("label", array(
			"text" => (isset($entity["area"])) ? SOYShop_Area::getAreaText($entity["area"]) : ""
		));
		
		$this->addLabel("color", array(
			"text" => (isset($entity["color"])) ? $entity["color"] : "#FFFFFF"
		));
		
		$this->addLabel("occurrences", array(
			"text" => (isset($entity["occurrences"])) ? $entity["occurrences"] : 0
		));
	}
}
?>