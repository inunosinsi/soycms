<?php
/**
 * @entity SOYShop_AutoRanking
 */
abstract class SOYShop_AutoRankingDAO extends SOY2DAO{
	
	/**
	 * @return id
	 * @trigger onInsert
	 */
	abstract function insert(SOYShop_AutoRanking $bean);
	
	/**
	 * @trigger onUpdate
	 */
	abstract function update(SOYShop_AutoRanking $bean);
	
	/**
	 * @return list
	 * @order id DESC
	 */
	abstract function get();
	
	/**
	 * @return object
	 * @order id DESC
	 * @limit 1
	 */
	abstract function getLatestRanking();
	
	function getRanking($start, $end, $limit){
		
		$sql = "SELECT s.item_id, SUM(s.item_count) AS item_count FROM soyshop_orders s ".
				"INNER JOIN soyshop_order o ".
				"ON s.order_id = o.id ".
				"WHERE o.order_date >= :start ".
				"AND o.order_date <= :now ".
				"GROUP BY s.item_id ".
				"ORDER BY item_count DESC ".
				"LIMIT " . $limit;
		
		$binds = array(":start" => $start, ":now" => $end);
		try{
			$results = $this->executeQuery($sql, $binds);
		}catch(Exception $e){
			return array();
		}
		
		//商品IDのみ取得
		$itemIds = array();
		foreach($results as $result){
			$itemIds[] = (int)$result["item_id"];
		}
		return $itemIds;
	}
	
	function getRankingList(){
		
		try{
			$obj = $this->getLatestRanking();
		}catch(Exception $e){
			return array();
		}
		
		$rankList = array();
		if(!is_null($obj->getContent())){
			$rankList = explode(",", $obj->getContent());
		}
		
		return $rankList;
	}
	
	/**
	 * @final
	 */
	function onInsert($query, $binds){
		
		if(!isset($binds[":createDate"])){
			$binds[":createDate"] = time();
		}
		return array($query, $binds);
	}
	
	/**
	 * @final
	 */
	function onUpdate($query, $binds){
		return array($query, $binds);
	}
}
?>