<?php

class CustomerLogic extends SOY2LogicBase{
	
	private $dao;
	
	function __construct(){
		$this->dao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
	}
	
	function calc(){
		list($start, $end) = self::getStartAndEnd();
		
		//指定の期間の注文をすべて取得する
		try{
			$res = $this->dao->executeQuery(self::buildSql(), array(":start" => $start, ":end" => $end));
		}catch(Exception $e){
			$res = array();
		}
		
		if(!count($res)) return array();
		
		//user_id => array(price, order_id => array())
		$list = array();
		$logic = SOY2Logic::createInstance("module.plugins.common_aggregate.logic.AggregateLogic");
		foreach($res as $vals){
			if(array_key_exists($vals["user_id"], $list)){
				$list[$vals["user_id"]]["price"] += $logic->calc($vals);
				$list[$vals["user_id"]]["order_ids"][] = (int)$vals["id"];
			}else{
				$v = array();
				$v["price"] = $logic->calc($vals);
				$v["order_ids"][] = (int)$vals["id"];
				$v["user_id"] = (int)$vals["user_id"];
				$list[$vals["user_id"]] = $v;
			}
		}
		
		//ソート用の配列
		$sort_keys = array();
		foreach($list as $key => $l){
			if(isset($l["price"])){
				$sort_keys[$key] = (int)$l["price"];
			}
		}
		
		//配列を設定に従い整列
		array_multisort($sort_keys, SORT_DESC, $list);
		
		$lines = array();
		
		foreach($list as $vals){
			$line = array();
			$line[] = self::getUserNameByUserId($vals["user_id"]);	//名前
			$line[] = $vals["price"];						//合計金額
			
			$itemList = self::getPurcharedItemNameList($vals["order_ids"]);
			$items = array();
			foreach($itemList as $i){
				$items[] = $i["item_name"];
			}
			$line[] = implode(",", $items);
			
			$lines[] = implode(",", $line);
		}

		return $lines;
	}
	
	private function buildSql(){
		return "SELECT id, price, user_id, modules FROM soyshop_order ".
				"WHERE order_status > " . SOYShop_Order::ORDER_STATUS_INTERIM . " ".
				"AND order_status < " . SOYShop_Order::ORDER_STATUS_CANCELED . " ".
				"AND order_date >= :start ".
				"AND order_date <= :end";
	}
	
	private function getUserNameByUserId($userId){
		try{
			$res = $this->dao->executeQuery("SELECT name FROM soyshop_user WHERE id = :user_id", array(":user_id" => $userId));
		}catch(Exception $e){
			return "";
		}
		
		return (isset($res[0]["name"])) ? $res[0]["name"] : "";
	}
		
	private function getPurcharedItemNameList($orderIds){
		$sql = "SELECT DISTINCT item_name FROM soyshop_orders ".
				"WHERE order_id IN (" . implode(",", $orderIds) . ") ";
				
		try{
			return $this->dao->executeQuery($sql);
		}catch(Exception $e){
			return array();
		}
	}
	
	function getLabels(){
		$label = array();
		$label[] = "顧客名";
		$label[] = "購入合計";
		$label[] = "購入商品";
		
		return $label;
	}
	
	private function getStartAndEnd(){
		
		if(!isset($_POST["Customer"])) return array(mktime(0,0,0,1,1,date("Y")), mktime(0,0,0,12,31,date("Y"))+24*60*60);
		
		$y = (int)$_POST["Customer"]["year"];
		if(!isset($_POST["Customer"]["month"]) || (int)$_POST["Customer"]["month"] === 0){
			return array(mktime(0,0,0,1,1,$y), mktime(0,0,0,12,31,$y)+24*60*60);
		}
		
		$m = (int)$_POST["Customer"]["month"];
		
		//日付を指定
		if(isset($_POST["Customer"]["day"]) && (int)$_POST["Customer"]["day"]){
			$start = mktime(0,0,0,$m,$_POST["Customer"]["day"],$y);
			$end = $start + 24*60*60;
		}else{
			$start = mktime(0,0,0,$m,1,$y);
			if($m === 12){
				$end = mktime(0,0,0,1,1,$y+1) - 1;
			}else{
				$end = mktime(0,0,0,$m+1,1,$y) - 1;
			}
		}
		
		return array($start, $end);
	}
}
?>