<?php

class Analytics_LanguagePage extends Analytics_CommonPage{
	
	private $orderDao;
	
	function build_print(){
		
		$this->orderDao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
		
		//共通部分の表示
		$this->buildCommon();
		
		$start = AnalyticsPluginUtil::convertTitmeStamp("start");
		$end = AnalyticsPluginUtil::convertTitmeStamp("end");

		$this->createAdd("language_graph_list", "LanguageGraphListComponent", array(
			"list" => $this->calc($start, $end)
		));
	}
	
	function calc($start, $end){
		
		try{
			$results = $this->orderDao->executeQuery($this->buildSql(), array(":start" => $start, ":end" => $end));
			if(count($results) === 0) return array();
		}catch(Exception $e){
			return array();
		}
		
		$langs = array();
		
		foreach($results as $res){
			$attrs = soy2_unserialize($res["attributes"]);
			if(isset($attrs["util_multi_language"]["value"])){
				if(isset($langs[$attrs["util_multi_language"]["value"]])){
					$langs[$attrs["util_multi_language"]["value"]]["occurrences"]++;
				}else{
					$langs[$attrs["util_multi_language"]["value"]]["occurrences"] = 1;
				}
			}
		}
		
		arsort($langs);		//キーを維持しつつ、降順にソート
		
		foreach($langs as $key => $lang){
			$r = rand(0,255);
			$g = rand(0,255);
			$b = rand(0,255);
			$a = "0.".rand(1,9);
			$langs[$key]["color"] = "rgba(" . $r . ", " . $g . ", " . $b . ", " . $a . ")";
		}
		
		return $langs;
	}
	
	function buildSql(){
		$sql = "SELECT attributes ".
				"FROM soyshop_order ".
				"WHERE order_status > " . SOYShop_Order::ORDER_STATUS_INTERIM . " ".
				"AND order_status < " . SOYShop_Order::ORDER_STATUS_CANCELED . " ".
				"AND order_date >= :start ".
				"AND order_date <= :end";
		
		if(isset($_POST["AnalyticsPlugin"]["limit"]) && strlen($_POST["AnalyticsPlugin"]["limit"]) > 0 && is_numeric($_POST["AnalyticsPlugin"]["limit"])){
			$sql .= " LIMIT " . (int)$_POST["AnalyticsPlugin"]["limit"];
		}
		
		return $sql;
	}
}

class LanguageGraphListComponent extends HTMLList{
	
	protected function populateItem($entity, $key) {
		$this->addLabel("label", array(
			"text" => (isset($key)) ? $key : ""
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