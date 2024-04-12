<?php

/**
 * @entity SOYShop_SalePeriod
 */

abstract class SOYShop_SalePeriodDAO extends SOY2DAO{
	
   	abstract function insert(SOYShop_SalePeriod $bean);
   	
   	/**
   	 * @return object
   	 */
   	abstract function getByItemId($itemId);
	
	abstract function deleteByItemId($itemId);
	
	function checkOnSale($itemId, $date = null){
		
		if(is_null($date)) $date = time();
		
		$sql = "SELECT item_id " .
				"FROM soyshop_sale_period ".
				"WHERE item_id = :itemId ".
				"AND sale_period_start < :start ".
				"AND sale_period_end > :end ".
				"LIMIT 1";
		$binds = array(":itemId" => $itemId, ":start" => $date, ":end" => $date);
		
		try{
			$res = $this->executeQuery($sql, $binds);
		}catch(Exception $e){
			return false;
		}
		
		return (count($res) > 0);
	}
	
	/**
	 * セール終了間近
	 */
	function getItemNearSaleEnd($before = 5){
		$start = time() + 24 * 60 * 60 * $before;
		$end = $start + 24 * 60 * 60;
		
		$sql = "SELECT item.* ".
				"FROM soyshop_item item ".
				"INNER JOIN soyshop_sale_period sale ".
				"ON item.id = sale.item_id ".
				"WHERE item.item_sale_flag = 1 ".
				"AND item.open_period_start < " . $end . " ".
				"AND item.open_period_end > " . $end . " ".
				"AND item.item_is_open = 1 ".
				"AND item.is_disabled = 0 ".
				"AND sale.sale_period_end BETWEEN " . $start . " AND " . $end . "";
		try{
			$res = $this->executeQuery($sql, array());
		}catch(Exception $e){
			return array();
		}
		
		$array = array();
		$itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		foreach($res as $obj){
			if(!isset($obj["id"]) || (int)$obj["id"] === 0) continue;
			$array[$obj["id"]] = $itemDao->getObject($obj);
		}
		
		return $array;
 	}
	
	function getSaleItems($offset = 0, $limit = 10){
		
		$now = time();
		
		$sql = "SELECT item.* ".
				"FROM soyshop_item item ".
				"INNER JOIN soyshop_sale_period sale ".
				"ON item.id = sale.item_id ".
				"WHERE item.item_sale_flag = 1 ".
				"AND item.open_period_start < " . $now . " ".
				"AND item.open_period_end > " . $now . " ".
				"AND item.item_is_open = 1 ".
				"AND item.is_disabled = 0 ".
				"AND sale.sale_period_start < " . $now . " ".
				"AND sale.sale_period_end > " . $now . " ".
				"LIMIT " . $limit . " ".
				"OFFSET " . $limit * $offset;
		
		$binds = array();
		
		try{
			$res = $this->executeQuery($sql, $binds);
		}catch(Exception $e){
			var_dump($e);
			return array();
		}
		
		if(count($res) === 0) return array();
		
		$array = array();
		$itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		foreach($res as $obj){
			if(!isset($obj["id"]) || (int)$obj["id"] === 0) continue;
			$array[$obj["id"]] = $itemDao->getObject($obj);
		}
		
		//配列の0番目の値として返してほしいらしい
		return array($array);
	}
	
	function countSaleItems(){
		$now = time();
		
		$sql = "SELECT COUNT(*) AS item_count ".
				"FROM soyshop_item item ".
				"INNER JOIN soyshop_sale_period sale ".
				"ON item.id = sale.item_id ".
				"WHERE item.item_sale_flag = 1 ".
				"AND item.open_period_start < " . $now . " ".
				"AND item.open_period_end > " . $now . " ".
				"AND item.item_is_open = 1 ".
				"AND item.is_disabled = 0 ".
				"AND sale.sale_period_start < " . $now . " ".
				"AND sale.sale_period_end > " . $now;
				
		$binds = array();
		
		try{
			$res = $this->executeQuery($sql, $binds);
		}catch(Exception $e){
			return 0;
		}
		
		return (isset($res[0]["item_count"])) ? (int)$res[0]["item_count"] : 0;
	}
}