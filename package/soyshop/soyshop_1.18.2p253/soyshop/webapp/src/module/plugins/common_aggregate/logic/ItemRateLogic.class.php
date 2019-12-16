<?php

class ItemRateLogic extends SOY2LogicBase{
	
	function __construct(){
		SOY2::import("module.plugins.common_aggregate.util.AggregateUtil");
		SOY2::import("domain.order.SOYShop_Order");
	}
	
	function calc(){
		$start = AggregateUtil::convertTitmeStamp("start");
		$end = AggregateUtil::convertTitmeStamp("end");
				
		$itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		
		$sql = self::buildSql();
		$lim = (isset($_POST["Aggregate"]["limit"]) && (int)$_POST["Aggregate"]["limit"] > 0) ? (int)$_POST["Aggregate"]["limit"] : 10;
		$sql .= " LIMIT " . $lim;
		
		try{
			$results = $itemDao->executeQuery($sql, array(":start" => $start, ":end" => $end));
		}catch(Exception $e){
			return array();
		}
		
		if(!count($results)) return array();
		
		//ソート用の配列
		$sort_keys = array();
		foreach($results as $key => $result){
			if(isset($result["TOTAL"])){
				$sort_keys[$key] = (int)$result["TOTAL"];
			}
		}
		
		//配列を設定に従い整列
		array_multisort($sort_keys, SORT_DESC, $results);
/**
		if($_POST["Aggregate"]["limit"] > 0){
			if(count($results) > (int)$_POST["Aggregate"]["limit"]){
				array_splice($results, (int)$_POST["Aggregate"]["limit"]);
			}
		}
**/
		
		$array = array();
		$rank = 1;
		foreach($results as $res){
			$list = array();
			
			$list["rank"] = $rank++;
			$list["item_code"] = $res["item_code"];
			$list["item_name"] = $res["item_name"];
			$list["item_price"] = $res["item_price"];
			$list["count"] = $res["COUNT"];
			$list["total"] = $res["TOTAL"];
			
			$array[] = implode(",", $list);
		}
		
		return $array;
	}
	
	private function buildSql(){
		return "SELECT os.item_id, SUM(os.item_count) AS COUNT, SUM(os.total_price) AS TOTAL, item.item_name, item.item_code, os.item_price ".
				"FROM soyshop_orders os ".
				"INNER JOIN soyshop_order o ".
				"ON o.id = os.order_id ".
				"INNER JOIN soyshop_item item ".
				"ON os.item_id= item.id ".
				"WHERE o.order_date >= :start ".
				"AND o.order_date <= :end " .
				"AND o.order_status > " . SOYShop_Order::ORDER_STATUS_INTERIM . " ".
				"AND o.order_status < " . SOYShop_Order::ORDER_STATUS_CANCELED . " ".
				"GROUP BY os.item_id, os.item_price ".
				"ORDER BY COUNT DESC";
	}
	
	function getLabels(){
		$label = array();
		$label[] = "順位";
		$label[] = "商品番号";
		$label[] = "商品名";
		$label[] = "商品単価";
		$label[] = "購入件数";
//		$label[] = "点数";
		$label[] = "金額";
		
		return $label;
	}
}
?>