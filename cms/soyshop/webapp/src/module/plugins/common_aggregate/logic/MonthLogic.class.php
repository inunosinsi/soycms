<?php

class MonthLogic extends SOY2LogicBase{
	
	private $dao;
		
	function __construct(){
		SOY2::import("module.plugins.common_aggregate.util.AggregateUtil");
		SOY2::import("domain.user.SOYShop_User");
		$this->dao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
	}
	
	function calc(){
		$start = AggregateUtil::convertTitmeStamp("start");
		$end = AggregateUtil::convertTitmeStamp("end");
				
		$values = array();
		
		$startYear = date("Y", $start);
		$endYear = date("Y", $end);
		
		
		$yearDiff = $endYear - $startYear;
		
		//一年内ならば
		if($yearDiff === 0){
			$startYearMonth = date("n", $start);
			$endYearMonth = date("n", $end);
			
			for($i = $startYearMonth; $i <= $endYearMonth; $i++){
				$s = mktime(0, 0, 0, $i, 1, $startYear);
				$en = mktime(0, 0, 0, $i + 1, 1, $startYear) - 1;
				
				$values[] = self::adjustGenderSQLResult($s, $en);
			}
			
		//数年分
		}else{
			$yearCounter = 0;
			
			for($i = $yearCounter; $i <= $yearDiff; $i++){
				//最初の年
				if($i === 0){
					$startYearMonth = date("n", $start);
					
					for($j = $startYearMonth; $j <= 12; $j++){
						$s = mktime(0, 0, 0, $j, 1, $startYear);
						$en = mktime(0, 0, 0, $j + 1, 1, $startYear) - 1;
				
						$values[] = self::adjustGenderSQLResult($s, $en);
					}
					
				//間の年
				}else if($i > 0 && $i < $yearDiff){
					
					$thisYear = $startYear + $i;
					
					for($j = 1; $j <= 12; $j++){
						$s = mktime(0, 0, 0, $j, 1, $thisYear);
						$en = mktime(0, 0, 0, $j + 1, 1, $thisYear) - 1;
						
						$values[] = self::adjustGenderSQLResult($s, $en);
					}
				
				//最後の年の処理
				}else if($i === $yearDiff){
					
					$thisYear = $startYear + $i;
					$endYearMonth = date("n", $end);
					
					for($j = 1; $j <= $endYearMonth; $j++){
						$s = mktime(0, 0, 0, $j, 1, $thisYear);
						$en = mktime(0, 0, 0, $j + 1, 1, $thisYear) - 1;
						
						$values[] = self::adjustGenderSQLResult($s, $en);
					}
				}
			}
		}
		
		return $values;
	}
	
	//SQLを実行する
	private function executeSql($sql, $start, $end){
		try{
			$res = $this->dao->executeQuery($sql, array(":start" => $start, ":end" => $end));
		}catch(Exception $e){
			$res = array();
		}
		
		return $res;
	}
	
	private function buildSql(){
		return "SELECT price, modules FROM soyshop_order ".
				"WHERE order_status > " . SOYShop_Order::ORDER_STATUS_INTERIM . " ".
				"AND order_status < " . SOYShop_Order::ORDER_STATUS_CANCELED . " ".
				"AND order_date >= :start ".
				"AND order_date <= :end";
	}
	
	//sex 0:男性 1:女性
	private function buildGenderSql($gender){
		$sql = "SELECT COUNT(user.id) AS COUNT FROM soyshop_user user ".
				"INNER JOIN soyshop_order o ".
				"ON user.id = o.user_id ".
				"WHERE o.order_status > " . SOYShop_Order::ORDER_STATUS_INTERIM . " ".
				"AND o.order_status < " . SOYShop_Order::ORDER_STATUS_CANCELED . " ".
				"AND o.order_date >= :start ".
				"AND o.order_date <= :end ";
		
		if($gender == SOYShop_User::USER_SEX_MALE){
			$sql .= "AND user.gender = " . SOYShop_User::USER_SEX_MALE . " ";
		}else{
			$sql .= "AND user.gender = " . SOYShop_User::USER_SEX_FEMALE . " ";
		}

		return $sql;
	}
	
	private function adjustGenderSQLResult($start, $end){
		$res = self::executeSql(self::buildSql(), $start, $end);
		$maleCntRes = self::executeSql(self::buildGenderSql(SOYShop_User::USER_SEX_MALE), $start, $end);
		$femaleCntRes = self::executeSql(self::buildGenderSql(SOYShop_User::USER_SEX_FEMALE), $start, $end);
						
		$list = array();
		$list["period"] = date("Y-m", $start);
		$list["count"] = count($res);
		$list["male"] = (isset($maleCntRes[0]["COUNT"])) ? (int)$maleCntRes[0]["COUNT"] : 0;
		$list["female"] = (isset($femaleCntRes[0]["COUNT"])) ? (int)$femaleCntRes[0]["COUNT"] : 0;
		
		//金額の調整
		$totalPrice = 0;
		$logic = SOY2Logic::createInstance("module.plugins.common_aggregate.logic.AggregateLogic");
		foreach($res as $v){
			$totalPrice += $logic->calc($v);
		}
		
		$list["total"] = $totalPrice;
		$list["average"] = ($list["total"] > 0 && $list["count"] > 0) ? floor($list["total"] / $list["count"]) : 0;
		
		return implode(",", $list);
	}
	
	function getLabels(){
		$label = array();
		$label[] = "期間";
		$label[] = "購入件数";
		$label[] = "男性";
		$label[] = "女性";
		$label[] = "購入合計";
		$label[] = "購入平均";
		
		return $label;
	}
}
?>