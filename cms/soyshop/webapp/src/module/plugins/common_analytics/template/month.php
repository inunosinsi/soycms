<?php
class Analytics_MonthPage extends Analytics_CommonPage{
	
	private $orderDao;
	
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
	
		//集計
		$this->addLabel("result_values", array(
			"text" => implode(",", $this->calc($start, $end))		
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
		try{
			$res = $this->orderDao->executeQuery($this->buildSql(), array(":start" => $start, ":end" => $end));
			$total = (isset($res[0]["SUM(os.total_price)"])) ? (int)$res[0]["SUM(os.total_price)"] : 0;
		}catch(Exception $e){
			$total = 0;
		}
		return $total;
	}
	
	function buildSql(){
		return "SELECT SUM(os.total_price) FROM soyshop_order o ".
				"INNER JOIN soyshop_orders os ".
				"ON o.id = os.order_id ".
				"WHERE o.order_status > " . SOYShop_Order::ORDER_STATUS_INTERIM . " ".
				"AND o.order_status < " . SOYShop_Order::ORDER_STATUS_CANCELED . " ".
				"AND o.order_date >= :start ".
				"AND o.order_date <= :end";
	}
}
?>