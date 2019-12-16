<?php

class Analytics_RepeatmonthPage extends Analytics_CommonPage{
		
	private $orderDao;
	
	//一回目の検索結果を保持しておく。月ごとの新規顧客による注文数
	private $results;
	
	//範囲の数を保持して奥
	private $rangeCount;
	
	function build_print(){
		
		$this->orderDao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
		
		//共通部分の表示
		$this->buildCommon();
		
		$start = AnalyticsPluginUtil::convertTitmeStamp("start");
		$end = AnalyticsPluginUtil::convertTitmeStamp("end");
		
		//範囲
		$this->addLabel("date_range", array(
			"html" => AnalyticsPluginUtil::buildDateRange($start, $end)
		));
		
		$this->addLabel("result_repeat", array(
			"text" => implode(",", $this->calc($start, $end))
		));
	}
	
	//リピート率を調べる (累積注文数 - 累積新規注文数) / 累積注文数
	function calc($start, $end){
		$values = array();
		
		$startYear = date("Y", $start);
		$endYear = date("Y", $end);
		
		$yearDiff = $endYear - $startYear;
		
		//一年内ならば
		if($yearDiff === 0){
			$startYearMonth = date("n", $start);
			$endYearMonth = date("n", $end);
			
			for($i = $startYearMonth; $i <= $endYearMonth; $i++){
				$values[] = $this->executeSql(mktime(0, 0, 0, $i, 1, $startYear), mktime(0, 0, 0, $i + 1, 1, $startYear) - 1);
			}
			
		//数年分
		}else{
			$yearCounter = 0;
			
			for($i = $yearCounter; $i <= $yearDiff; $i++){
				//最初の年
				if($i === 0){
					$startYearMonth = date("n", $start);
					
					for($j = $startYearMonth; $j <= 12; $j++){
						$values[] = $this->executeSql(mktime(0, 0, 0, $j, 1, $startYear), mktime(0, 0, 0, $j + 1, 1, $startYear));
					}
					
				//間の年
				}else if($i > 0 && $i < $yearDiff){
					
					$thisYear = $startYear + $i;
					
					for($j = 1; $j <= 12; $j++){
						$values[] = $this->executeSql(mktime(0, 0, 0, $j, 1, $thisYear), mktime(0, 0, 0, $j + 1, 1, $thisYear) - 1);
					}
										
				//最後の年の処理
				}else if($i === $yearDiff){
					
					$thisYear = $startYear + $i;
					$endYearMonth = date("n", $end);
					
					for($j = 1; $j <= $endYearMonth; $j++){
						$values[] = $this->executeSql(mktime(0, 0, 0, $j, 1, $thisYear), mktime(0, 0, 0, $j + 1, 1, $thisYear) - 1);
					}
				}
			}
		}
		
		return $values;
		
	}
	
	//SQLを実行する
	function executeSql($start, $end){
		
		//月毎の総注文数
		try{
			$totalResults = $this->orderDao->executeQuery($this->buildTotalSql(), array(":start" => $start, ":end" => $end));
		}catch(Exception $e){
			return 0;
		}
		
		if(!isset($totalResults[0]["total_count"]) || (int)$totalResults[0]["total_count"] === 0) return 0;
		$totalCount = (int)$totalResults[0]["total_count"];
		
		//新規注文数
		if(!$this->results){
			try{
				$this->results = $this->orderDao->executeQuery($this->buildFirstOrderSql());
			}catch(Exception $e){
				$this->results = array();
			}
		}
		
		$newOrderCount = 0;
		foreach($this->results as $key => $result){
			
			if(!isset($result["order_date_min"])) continue;
			
			//小さい値の者は予め配列から除いておく
			if($result["order_date_min"] < $start) unset($this->results[$key]);
			
			if($result["order_date_min"] >= $start && $result["order_date_min"] <= $end){
				$newOrderCount++;
				unset($this->results[$key]);
			}
		}
			
		return round(($totalCount - $newOrderCount) / $totalCount * 100, 1);
	}
	
	//今月の総注文数
	function buildTotalSql(){
		return "SELECT COUNT(id) total_count FROM soyshop_order ".
				"WHERE order_status > " . SOYShop_Order::ORDER_STATUS_INTERIM . " ".
				"AND order_status < " . SOYShop_Order::ORDER_STATUS_CANCELED . " ".
				"AND order_date >= :start ".
				"AND order_date <= :end";
	}

	//今月はじめての注文
	function buildFirstOrderSql(){
		return "SELECT o.user_id, MIN(o.order_date) order_date_min " .
				"FROM soyshop_order o " .
				"INNER JOIN soyshop_user user ".
				"ON o.user_id = user.id ".
				"WHERE order_status > " . SOYShop_Order::ORDER_STATUS_INTERIM . " ".
				"AND order_status < " . SOYShop_Order::ORDER_STATUS_CANCELED . " ".
				"AND user.is_disabled != " . SOYShop_User::USER_IS_DISABLED . " ".
				"GROUP BY o.user_id";
	}

/**	
	//一回のみ注文の新規顧客数
	function buildSql(){
		return "SELECT COUNT(user_id) new_customer_count FROM soyshop_order ".
				"WHERE user_id IN (" .
					"SELECT o.user_id FROM soyshop_order o ".
					"INNER JOIN soyshop_user user ".
					"ON o.user_id = user.id ".
					"WHERE o.order_status > " . SOYShop_Order::ORDER_STATUS_INTERIM . " ".
					"AND o.order_status < " . SOYShop_Order::ORDER_STATUS_CANCELED . " ".
					"AND user.is_disabled != " . SOYShop_User::USER_IS_DISABLED . " ".
					"GROUP BY o.user_id ".
					"HAVING COUNT(o.user_id) = 1".
				") ".
				"AND order_status > " . SOYShop_Order::ORDER_STATUS_INTERIM . " ".
				"AND order_status < " . SOYShop_Order::ORDER_STATUS_CANCELED . " ".
				"AND order_date >= :start ".
				"AND order_date <= :end ";
	}
**/
}
?>