<?php

class Analytics_NewcustomerPage extends Analytics_CommonPage{

	private $orderDao;
	
	//全注文を取得する
	private $results;

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
		
		$this->addLabel("total_color", array(
			"text" => "rgba(220,220,220,1)"
		));
		
		$this->addLabel("new_color", array(
			"text" => "rgba(151,187,205,1)"
		));
		
		//総注文数
		$totalArray = $this->calcTotal($start, $end);
		$this->addLabel("result_total", array(
			"text" => implode(",", $totalArray)
		));
		
		//新規注文数
		$calcArray = $this->calc($start, $end);
		$this->addLabel("result_newcustomer", array(
			"text" => implode(",", $calcArray)
		));
		
		//新規注文率
		$this->addLabel("result_rate", array(
			"text" => implode(",", $this->calcRate($totalArray, $calcArray))
		));
	}
	
	//都度、SQL構文を実行する
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
						$values[] = $this->executeSql(mktime(0, 0, 0, $j, 1, $startYear), mktime(0, 0, 0, $j + 1, 1, $startYear) - 1);
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
		
		if(!$this->results){
			try{
				$this->results = $this->orderDao->executeQuery($this->buildSql());
			}catch(Exception $e){
				return 0;
			}
		}
			
		if(count($this->results) === 0) return 0;
		
		$newCustomerTotal = 0;
		foreach($this->results as $key => $result){
			
			if(!isset($result["order_date_min"])) continue;
			
			//検索条件よりも前の注文の場合はすべて削除する
			if($result["order_date_min"] < $start) unset($this->results[$key]);
			
			//今月はじめての注文であるかを調べる
			if($result["order_date_min"] >= $start && $result["order_date_min"] <= $end){
				$newCustomerTotal++;
				$this->results[$key];
			}
		}
		
		return $newCustomerTotal;
	}
	
	function buildSql(){
		return "SELECT MIN(order_date) order_date_min FROM soyshop_order o ".
				"INNER JOIN soyshop_user user ".
				"ON o.user_id = user.id ".
				"WHERE o.order_status > " . SOYShop_Order::ORDER_STATUS_INTERIM . " ".
				"AND o.order_status < " . SOYShop_Order::ORDER_STATUS_CANCELED . " ".
				"AND user.is_disabled != " . SOYShop_User::USER_IS_DISABLED . " ".
				"GROUP BY o.user_id";
	}	
	
	/** ここから総注文数 **/
	function calcTotal($start, $end){
		$values = array();
		
		$startYear = date("Y", $start);
		$endYear = date("Y", $end);
		
		$yearDiff = $endYear - $startYear;
		
		//一年内ならば
		if($yearDiff === 0){
			$startYearMonth = date("n", $start);
			$endYearMonth = date("n", $end);
			
			for($i = $startYearMonth; $i <= $endYearMonth; $i++){
				$values[] = $this->executeTotalSql(mktime(0, 0, 0, $i, 1, $startYear), mktime(0, 0, 0, $i + 1, 1, $startYear) - 1);
			}
			
		//数年分
		}else{
			$yearCounter = 0;
			
			for($i = $yearCounter; $i <= $yearDiff; $i++){
				//最初の年
				if($i === 0){
					$startYearMonth = date("n", $start);
					
					for($j = $startYearMonth; $j <= 12; $j++){
						$values[] = $this->executeTotalSql(mktime(0, 0, 0, $j, 1, $startYear), mktime(0, 0, 0, $j + 1, 1, $startYear) - 1);
					}
					
				//間の年
				}else if($i > 0 && $i < $yearDiff){
					
					$thisYear = $startYear + $i;
					
					for($j = 1; $j <= 12; $j++){
						$values[] = $this->executeTotalSql(mktime(0, 0, 0, $j, 1, $thisYear), mktime(0, 0, 0, $j + 1, 1, $thisYear) - 1);
					}
										
				//最後の年の処理
				}else if($i === $yearDiff){
					
					$thisYear = $startYear + $i;
					$endYearMonth = date("n", $end);
					
					for($j = 1; $j <= $endYearMonth; $j++){
						$values[] = $this->executeTotalSql(mktime(0, 0, 0, $j, 1, $thisYear), mktime(0, 0, 0, $j + 1, 1, $thisYear) - 1);
					}
				}
			}
		}
		
		return $values;
	}
	
	function executeTotalSql($start, $end){
		
		try{
			$res = $this->orderDao->executeQuery($this->buildTotalSql(), array(":start" => $start, ":end" => $end));
		}catch(Exception $e){
			return 0;	
		}
				
		return (isset($res[0]["COUNT(id)"])) ? (int)$res[0]["COUNT(id)"] : 0;
	}
	
	function buildTotalSql(){
		return "SELECT COUNT(id) FROM soyshop_order ".
				"WHERE order_status > " . SOYShop_Order::ORDER_STATUS_INTERIM . " ".
				"AND order_status < " . SOYShop_Order::ORDER_STATUS_CANCELED . " ".
				"AND order_date >= :start ".
				"AND order_date <= :end";
	}
	
	/** ここから新規注文率 **/
	
	function calcRate($totalArray, $calcArray){
		
		$rateArray = array();
		for ($i = 0; $i < count($totalArray); $i++){
			$total = (int)$totalArray[$i];
			$calc = (int)$calcArray[$i];
			if($total === 0 || $calc === 0){
				$rateArray[] = 0;
				continue;
			}
			
			$rateArray[] = round($calc / $total * 100, 1);
		}
		
		return $rateArray;
	}
}
?>