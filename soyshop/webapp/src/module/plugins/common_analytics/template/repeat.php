<?php

class Analytics_RepeatPage extends Analytics_CommonPage{
	
	const DIVISION = 8;
	
	private $userDao;
	
	//集計結果を保持しておく
	private $results;
	
	function build_print(){
		
		$this->userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
		
		//共通部分の表示
		$this->buildCommon();
		
		$start = AnalyticsPluginUtil::convertTitmeStamp("start", false);
		$end = AnalyticsPluginUtil::convertTitmeStamp("end");
		
		SOY2::import("domain.order.SOYShop_Order");
		$range = $this->getRange($start, $end);
		
		//範囲を取得
		$this->addLabel("repeat_range", array(
			"html" => implode(",", $this->buildRange($range))
		));
		
		$this->addLabel("result_repeat", array(
			"text" => implode(",", $this->calc($start, $end, $range))
		));
		
		$repeater = $this->getRepeater($start, $end);
		$total = $this->getTotal($start, $end);
		
		$this->addLabel("repeater", array(
			"text" => $repeater
		));
		
		$this->addLabel("total", array(
			"text" => $total
		));
		
		$this->addLabel("repeater_rate", array(
			"text" => round($repeater / $total * 100, 1)
		));
		
		$this->buildForm();
	}
	
	function buildForm(){
		
		$this->addForm("form");

		$this->addCheckBox("same_send_address", array(
			"name" => "same",
			"value" => 1,
			"selected" => false,
			"label" => "請求先と送り先が一致する注文は除く"
		));
		
		$this->addInput("plugin", array(
			"name" => "plugin",
			"value" => (isset($_POST["plugin"])) ? $_POST["plugin"] : ""
		));
		
		$this->addInput("plugin_type", array(
			"name" =>  "AnalyticsPlugin[type]",
			"value" => (isset($_POST["AnalyticsPlugin"]["type"])) ? $_POST["AnalyticsPlugin"]["type"] : ""
		));
		
		$this->addInput("plugin_repeat_start", array(
			"name" => "AnalyticsPlugin[period][start]",
			"value" => (isset($_POST["AnalyticsPlugin"]["period"]["start"])) ? $_POST["AnalyticsPlugin"]["period"]["start"] : ""
		));
		
		$this->addInput("plugin_repeat_end", array(
			"name" => "AnalyticsPlugin[period][end]",
			"value" => (isset($_POST["AnalyticsPlugin"]["period"]["end"])) ? $_POST["AnalyticsPlugin"]["period"]["end"] : ""
		));
	}
	
	//計算の範囲を取得する
	function getRange($start, $end){		
		try{
			$results = $this->userDao->executeQuery($this->buildRangeSql(), array(":start" => $start, ":end" => $end));
		}catch(Exception $e){
			return array();
		}
		
		if(count($results) === 0) return array();
		
		//最高と最低の値をとる
		$countArray = array();
		foreach($results as $result){
			if(isset($result["occurrences"])){
				$countArray[] = (int)$result["occurrences"];
			}
		}
		
		$max = max($countArray);
		$min = min($countArray);
		
		$interval = ceil($max / self::DIVISION);
		
		$range = array();
		for ($i = 0; $i < self::DIVISION; $i++){
			if($i === 0) $range[] = 0;
			if($i === self::DIVISION - 1) $i *= 10;	//念の為、最後は幅を大きくとる
			$addition = $min + ($interval * $i);
			$range[] = (int)$addition;
		}
		
		return $range;
	}
	
	function buildRangeSql(){
		return "SELECT COUNT(user.id) occurrences FROM soyshop_user user ".
				"INNER JOIN soyshop_order o ".
				"ON user.id = o.user_id ".
				"WHERE o.order_status > " . SOYShop_Order::ORDER_STATUS_INTERIM . " ".
				"AND o.order_status < " . SOYShop_Order::ORDER_STATUS_CANCELED . " ".
				"AND o.order_date >= :start ".
				"AND o.order_date <= :end ".
				"AND user.is_disabled != " . SOYShop_User::USER_IS_DISABLED . " ".
				"GROUP BY user.id ";
	}
	
	function buildRange($range){
				
		$rangeTextArray = array();
		$last = self::DIVISION;
		for ($i = 0; $i < $last; $i++){
			if($i === 0){
				$rangeTextArray[] = "\"1\"";
			}else if($i === $last - 1){
				$rangeTextArray[] = "\"" . $range[$i] . "～\"";
			}else{
				$rangeTextArray[] = "\"" . $range[$i] . "～" . $range[$i + 1] . "\"";
			}
			
		}
		
		return $rangeTextArray;
		
	}
	
	/** ここから集計 **/
	
	function calc($start, $end, $range){
		$totalArray = array();
		
		if(!$this->results){
			try{
				$this->results = $this->userDao->executeQuery($this->buildSql(), array(":start" => $start, ":end" => $end));
			}catch(Exception $e){
				$this->results = array();
			}
		}
		
		//作成した範囲毎に集計していく
		for($i = 0; $i < count($range) - 1; $i++){
			$total = 0;
			foreach($this->results as $key => $result){
				if(!isset($result["occurrences"])) continue;
				
				if((int)$result["occurrences"] > $range[$i] && (int)$result["occurrences"] <= $range[$i + 1]){
					$total++;
					unset($this->results[$key]);
				}
			}
			
			$totalArray[] = $total;
		}
				
		return $totalArray;
	}
	
	function buildSql(){
		return "SELECT COUNT(user.id) occurrences FROM soyshop_user user ".
				"INNER JOIN soyshop_order o ".
				"ON user.id = o.user_id ".
				"WHERE o.order_status > " . SOYShop_Order::ORDER_STATUS_INTERIM . " ".
				"AND o.order_status < " . SOYShop_Order::ORDER_STATUS_CANCELED . " ".
				"AND o.order_date >= :start ".
				"AND o.order_date <= :end ".
				"AND user.is_disabled != " . SOYShop_User::USER_IS_DISABLED . " ".
				"GROUP BY user.id ".
				"ORDER BY occurrences";
	}
	
	/** リピーター **/
	function getRepeater($start, $end){
		$sql = "SELECT COUNT(user.id) occurrences FROM soyshop_user user ".
				"INNER JOIN soyshop_order o ".
				"ON user.id = o.user_id ".
				"WHERE o.order_status > " . SOYShop_Order::ORDER_STATUS_INTERIM . " ".
				"AND o.order_status < " . SOYShop_Order::ORDER_STATUS_CANCELED . " ".
				"AND o.order_date >= :start ".
				"AND o.order_date <= :end ".
				"AND user.is_disabled != " . SOYShop_User::USER_IS_DISABLED . " ".
				"GROUP BY user.id ".
				"HAVING occurrences > 1";
				
		try{
			$res = $this->userDao->executeQuery($sql, array(":start" => $start, ":end" => $end));
		}catch(Exception $e){
			return 0;
		}
		
		return (count($res));
	}
	
	/** 総ユニーク顧客数 **/
	function getTotal($start, $end){
		$sql = "SELECT COUNT(user.id) occurrences FROM soyshop_user user ".
				"INNER JOIN soyshop_order o ".
				"ON user.id = o.user_id ".
				"WHERE o.order_status > " . SOYShop_Order::ORDER_STATUS_INTERIM . " ".
				"AND o.order_status < " . SOYShop_Order::ORDER_STATUS_CANCELED . " ".
				"AND o.order_date >= :start ".
				"AND o.order_date <= :end ".
				"AND user.is_disabled != " . SOYShop_User::USER_IS_DISABLED . " ".
				"GROUP BY user.id ".
				"HAVING occurrences > 0";
				
		try{
			$res = $this->userDao->executeQuery($sql, array(":start" => $start, ":end" => $end));
		}catch(Exception $e){
			return 0;
		}
		
		return (count($res));
	}
}
?>