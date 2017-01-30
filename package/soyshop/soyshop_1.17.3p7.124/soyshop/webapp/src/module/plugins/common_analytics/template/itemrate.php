<?php

class Analytics_ItemratePage extends Analytics_CommonPage{
	
	private $itemOrderDao;
	
	function build_print(){
		
		$this->itemOrderDao = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO");
		
		//共通部分の表示
		$this->buildCommon();
		
		$start = AnalyticsPluginUtil::convertTitmeStamp("start");
		$end = AnalyticsPluginUtil::convertTitmeStamp("end");
		
		$this->createAdd("item_graph_list", "ItemGraphListComponent", array(
			"list" => $this->calc($start, $end),
			"itemDao" => SOY2DAOFactory::create("shop.SOYShop_ItemDAO")
		));
	}
	
	function calc($start, $end){
		
		try{
			$results = $this->itemOrderDao->executeQuery($this->buildSql(), array(":start" => $start, ":end" => $end));
		}catch(Exception $e){
			return array();
		}
		
		//ソート用の配列
		$sort_keys = array();
		foreach($results as $key => $result){
			if(isset($result["SUM(item_count)"])){
				$sort_keys[$key] = (int)$result["SUM(item_count)"];
			}
		}
		
		//配列を設定に従い整列
		array_multisort($sort_keys, SORT_DESC, $results);
		if($_POST["AnalyticsPlugin"]["limit"] > 0){
			if(count($results) > (int)$_POST["AnalyticsPlugin"]["limit"]){
				array_splice($results, (int)$_POST["AnalyticsPlugin"]["limit"]);
			}
		}
		
		for($i = 0; $i < count($results); $i++){
			$r = rand(0,255);
			$g = rand(0,255);
			$b = rand(0,255);
			$a = "0." . rand(1,9);
			$results[$i]["color"] = "rgba(" . $r . ", " . $g . ", " . $b . ", " . $a . ")";
		}
		
		return $results;
	}
	
	function buildSql(){
		$sql = "SELECT item_id, SUM(item_count) ".
				"FROM soyshop_orders ".
				"WHERE cdate >= :start ".
				"AND cdate <= :end " .
				"GROUP BY item_id ".
				"ORDER BY id DESC";
		
		return $sql;
	}
}

class ItemGraphListComponent extends HTMLList{
	
	private $itemDao;
	
	protected function populateItem($entity) {
		$itemId = (isset($entity["item_id"])) ? (int)$entity["item_id"] : null;
		$item = $this->getItem($itemId);
		
		$this->addLabel("label", array(
			"text" => $item->getName()
		));
		
		$this->addLabel("color", array(
			"text" => (isset($entity["color"])) ? $entity["color"] : "#FFFFFF"
		));
		
		$this->addLabel("itemcount", array(
			"text" => (isset($entity["SUM(item_count)"])) ? $entity["SUM(item_count)"] : 0
		));
	}
	
	function getItem($itemId){
		try{
			$item = $this->itemDao->getById($itemId);
		}catch(Exception $e){
			$item = new SOYShop_Item();
		}
		return $item;
	}
	
	function setItemDao($itemDao){
		$this->itemDao = $itemDao;
	}
}
?>