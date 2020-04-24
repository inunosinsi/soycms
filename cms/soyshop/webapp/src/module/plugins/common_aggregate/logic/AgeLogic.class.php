<?php

class AgeLogic extends SOY2LogicBase{
	
	private $dao;
	
	const AGE = 10;
	
	function __construct(){
		SOY2::import("module.plugins.common_aggregate.util.AggregateUtil");
		SOY2::import("domain.user.SOYShop_User");
		SOY2::import("domain.order.SOYShop_Order");
		$this->dao = new SOY2DAO();
	}
	
	function calc(){
		$results = array();
		
		$start = AggregateUtil::convertTitmeStamp("start");
		$end = AggregateUtil::convertTitmeStamp("end");
		
		//生年月日の登録がある人の集計
		$res = self::executeSql(self::buildSql(), $start, $end);
		
		//年代別に振り分ける
		if(count($res)) {
			$thisY = (int)date("Y");
			$list = array();
			for($i = 0; $i <= self::AGE; $i++){
				$list[] = array("count" => 0, "total" => 0);
			}
			
			foreach($res as $v){
				$birthArray = explode("-", $v["birthday"]);
				$age = $thisY - (int)$birthArray[0];
				for($i = 0; $i < 8; $i++){
					$s = $i * 10;
					$e = $s + 9;
					
					if($age >= $s && $age <= $e){
						$list[$i]["count"]++;
						$list[$i]["total"] = $list[$i]["total"] + (int)$v["price"];
					}
				}
			}
			
			//登録
			foreach($list as $n => $val){
				$values = array();
				$num = $n * 10;
				$values[] =  $num . "〜" . ($num + 9) . "歳";
				$values[] = $val["count"];
				$values[] = $val["total"];
				$values[] = ($val["count"] > 0) ? floor($val["total"] / $val["count"]) : 0;
				
				$results[] = implode(",", $values);
			}
		}
		
		//年齢、未回答
		$res = self::executeSql(self::buildNonAnswerSql(), $start, $end);
		$values = array();
		$values[] = "未回答";
		$cnt = (isset($res[0]["COUNT"])) ? (int)$res[0]["COUNT"] : 0;
		$values[] = $cnt;
		$total = (isset($res[0]["TOTAL"])) ? (int)$res[0]["TOTAL"] : 0;
		$values[] = $total;
		$values[] = ($cnt > 0) ? floor($total / $cnt) : 0;
		
		$results[] = implode(",", $values);
		
		return $results;
	}
	
	private function executeSql($sql, $start, $end){
		try{
			$res = $this->dao->executeQuery($sql, array(":start" => $start, ":end" => $end));
		}catch(Exception $e){
			$res = array();
		}
		
		return $res;
	}
	
	private function buildSql(){
		return "SELECT user.birthday, o.price FROM soyshop_user user ".
				"INNER JOIN soyshop_order o ".
				"ON user.id = o.user_id ".
				"WHERE o.order_status > " . SOYShop_Order::ORDER_STATUS_INTERIM . " ".
				"AND o.order_status < " . SOYShop_Order::ORDER_STATUS_CANCELED . " ".
				"AND o.order_date >= :start ".
				"AND o.order_date <= :end ".
				"AND (user.birthday IS NOT NULL || user.birthday != '') ";
	}
	
	private function buildNonAnswerSql(){
		return "SELECT COUNT(o.id) AS COUNT, SUM(o.price) AS TOTAL FROM soyshop_user user ".
				"INNER JOIN soyshop_order o ".
				"ON user.id = o.user_id ".
				"WHERE o.order_status > " . SOYShop_Order::ORDER_STATUS_INTERIM . " ".
				"AND o.order_status < " . SOYShop_Order::ORDER_STATUS_CANCELED . " ".
				"AND o.order_date >= :start ".
				"AND o.order_date <= :end ".
				"AND (user.birthday IS NULL || user.birthday = '') ";
	}
		
	function getLabels(){
		$label = array();
		$label[] = "年齢";
		$label[] = "購入件数";
		$label[] = "購入合計";
		$label[] = "購入平均";
		
		return $label;
	}
}