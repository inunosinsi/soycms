<?php

class Analytics_RepeatmonthPage extends Analytics_CommonPage{
		
	private $orderDao;
	
	function build_print(){
		
		$this->orderDao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
		
		//共通部分の表示
		$this->buildCommon();
		
		$start = AnalyticsPluginUtil::convertTitmeStamp("start", false);
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
		
		SOY2::import("domain.user.SOYShop_User");
		$this->addLabel("result_total", array(
			"text" => implode(",", $this->calcTotal($start, $end))
		));
		
		$this->addLabel("result_repeater", array(
			"html" => implode(",", $this->calc($start, $end))
		));
		
	}
	
	
	/** ここから集計 **/	
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
				$start = mktime(0, 0, 0, $i, 1, $startYear);
				$end = mktime(0, 0, 0, $i + 1, 1, $startYear) - 1;
								
				$values[] = $this->executeSql($start, $end);
			}
			
		//数年分
		}else{
			$yearCounter = 0;
			
			for($i = $yearCounter; $i <= $yearDiff; $i++){
				//最初の年
				if($i === 0){
					$startYearMonth = date("n", $start);
					
					for($j = $startYearMonth; $j <= 12; $j++){
						$start = mktime(0, 0, 0, $j, 1, $startYear);
						$end = mktime(0, 0, 0, $j + 1, 1, $startYear) - 1;
				
						$values[] = $this->executeSql($start, $end);
					}
					
				//間の年
				}else if($i > 0 && $i < $yearDiff){
					
					$thisYear = $startYear + $i;
					
					for($j = 1; $j <= 12; $j++){
						$start = mktime(0, 0, 0, $j, 1, $thisYear);
						$end = mktime(0, 0, 0, $j + 1, 1, $thisYear) - 1;
				
						$values[] = $this->executeSql($start, $end);
					}
										
				//最後の年の処理
				}else if($i === $yearDiff){
					
					$thisYear = $startYear + $i;
					$endYearMonth = date("n", $end);
					
					for($j = 1; $j <= $endYearMonth; $j++){
						$start = mktime(0, 0, 0, $j, 1, $thisYear);
						$end = mktime(0, 0, 0, $j + 1, 1, $thisYear) - 1;
				
						$values[] = $this->executeSql($start, $end);
					}
				}
			}
		}
		
		return $values;
	}
	
	//SQLを実行する
	function executeSql($start, $end){
		try{
			$results = $this->orderDao->executeQuery($this->buildSql(), array(":start" => $start, ":end" => $end));
		}catch(Exception $e){
			return 0;
		}

		$counter = 0;
		foreach($results as $result){
			$int = (int)$result["occurrences"];
			$userId = (isset($result["id"])) ? (int)$result["id"] : ((isset($result["user.id"])) ? (int)$result["user.id"] : null);
					
			try{
				$res = $this->orderDao->executeQuery($this->buildOccurrencesSql(), array("userId" => $userId, ":value1" => $int, ":value2" => $int + 1));
			}catch(Exception $e){
				continue;
			}

			if(isset($res[0]["occurrences"]) && $res[0]["occurrences"] > 1) $counter++;
		}
		
		return $counter;
	}
	
	function buildSql(){
		return "SELECT user.id, COUNT(user.id) occurrences FROM soyshop_user user ".
				"INNER JOIN soyshop_order o ".
				"ON user.id = o.user_id ".
				"WHERE o.order_status > " . SOYShop_Order::ORDER_STATUS_INTERIM . " ".
				"AND o.order_status < " . SOYShop_Order::ORDER_STATUS_CANCELED . " ".
				"AND o.order_date >= :start ".
				"AND o.order_date <= :end ".
				"AND user.is_disabled != " . SOYShop_User::USER_IS_DISABLED . " ".
				"GROUP BY user.id ".
				"HAVING occurrences > 1";
	}
	
	function buildOccurrencesSql(){
		return "SELECT COUNT(user.id) occurrences FROM soyshop_user user ".
				"INNER JOIN soyshop_order o ".
				"ON user.id = o.user_id ".
				"WHERE user.id = :userId ".
				"AND o.order_status > " . SOYShop_Order::ORDER_STATUS_INTERIM . " ".
				"AND o.order_status < " . SOYShop_Order::ORDER_STATUS_CANCELED . " ".
				"GROUP BY user.id ".
				"HAVING occurrences BETWEEN :value1 AND :value2";
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
				$start = mktime(0, 0, 0, $i, 1, $startYear);
				$end = mktime(0, 0, 0, $i + 1, 1, $startYear) - 1;
								
				$values[] = $this->executeTotalSql($start, $end);
			}
			
		//数年分
		}else{
			$yearCounter = 0;
			
			for($i = $yearCounter; $i <= $yearDiff; $i++){
				//最初の年
				if($i === 0){
					$startYearMonth = date("n", $start);
					
					for($j = $startYearMonth; $j <= 12; $j++){
						$start = mktime(0, 0, 0, $j, 1, $startYear);
						$end = mktime(0, 0, 0, $j + 1, 1, $startYear) - 1;
				
						$values[] = $this->executeTotalSql($start, $end);
					}
					
				//間の年
				}else if($i > 0 && $i < $yearDiff){
					
					$thisYear = $startYear + $i;
					
					for($j = 1; $j <= 12; $j++){
						$start = mktime(0, 0, 0, $j, 1, $thisYear);
						$end = mktime(0, 0, 0, $j + 1, 1, $thisYear) - 1;
				
						$values[] = $this->executeTotalSql($start, $end);
					}
										
				//最後の年の処理
				}else if($i === $yearDiff){
					
					$thisYear = $startYear + $i;
					$endYearMonth = date("n", $end);
					
					for($j = 1; $j <= $endYearMonth; $j++){
						$start = mktime(0, 0, 0, $j, 1, $thisYear);
						$end = mktime(0, 0, 0, $j + 1, 1, $thisYear) - 1;
				
						$values[] = $this->executeTotalSql($start, $end);
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
}
?>