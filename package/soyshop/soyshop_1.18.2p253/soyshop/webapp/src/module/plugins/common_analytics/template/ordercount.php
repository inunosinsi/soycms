<?php

class Analytics_OrdercountPage extends Analytics_CommonPage{

	private $userDao;

	function build_print(){
	
		$this->userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
	
		//共通部分の表示
		$this->buildCommon();
		
		$start = AnalyticsPluginUtil::convertTitmeStamp("start");
		$end = AnalyticsPluginUtil::convertTitmeStamp("end");

		SOY2::import("domain.config.SOYShop_Area");
		SOY2::import("domain.order.SOYShop_Order");
		$this->createAdd("repeater_ranking_list", "RepeaterRankingListComponent", array(
			"list" => $this->calc($start, $end),
		));
	}
	
	function calc($start, $end){
		try{
			$results = $this->userDao->executeQuery($this->buildSql(), array(":start" => $start, ":end" => $end));
		}catch(Exception $e){
			return array();
		}
		
		return $results;
	}
	
	function buildSql(){
		$sql = "SELECT name, reading, mail_address, area, COUNT(*) occurrences ".
				"FROM soyshop_user user ".
				"INNER JOIN soyshop_order o ".
				"ON user.id = o.user_id ".
				"WHERE o.order_status > " . SOYShop_Order::ORDER_STATUS_INTERIM . " ".
				"AND o.order_status < " . SOYShop_Order::ORDER_STATUS_CANCELED . " ".
				"AND o.order_date >= :start ".
				"AND o.order_date <= :end ".
				"AND user.is_disabled != " . SOYShop_User::USER_IS_DISABLED . " ".
				"GROUP BY user.mail_address ".
				"HAVING occurrences > 0 ".
				"ORDER BY occurrences DESC, user.reading ASC";
		
		if(isset($_POST["AnalyticsPlugin"]["limit"]) && strlen($_POST["AnalyticsPlugin"]["limit"]) > 0 && is_numeric($_POST["AnalyticsPlugin"]["limit"])){
			$sql .= " LIMIT " . (int)$_POST["AnalyticsPlugin"]["limit"];
		}
		
		return $sql;
	}
}

class RepeaterRankingListComponent extends HTMLList{
		
	protected function populateItem($entity, $key, $counter) {
		
		$this->addLabel("ranking", array(
			"text" => $counter
		));
		
		$this->addLabel("name", array(
			"text" => (isset($entity["name"])) ? $entity["name"] : ""
		));
		
		$this->addLabel("reading", array(
			"text" => (isset($entity["reading"])) ? $entity["reading"] : ""
		));
		
		$this->addLabel("area", array(
			"text" => (isset($entity["area"])) ? SOYShop_Area::getAreaText($entity["area"]) : ""
		));
		
		$this->addLink("mail_address", array(
			"link" => "mailto:" . $entity["mail_address"],
			"text" => $entity["mail_address"]
		));
		
		$this->addLabel("occurrences", array(
			"text" => (isset($entity["occurrences"])) ? (int)$entity["occurrences"] : 0
		));
	}
}
?>