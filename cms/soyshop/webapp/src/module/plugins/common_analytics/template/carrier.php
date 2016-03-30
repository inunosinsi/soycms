<?php

class Analytics_CarrierPage extends Analytics_CommonPage{
	
	private $orderDao;
	
	function build_print(){
		
		$this->orderDao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
		
		//共通部分の表示
		$this->buildCommon();
		
		$start = AnalyticsPluginUtil::convertTitmeStamp("start");
		$end = AnalyticsPluginUtil::convertTitmeStamp("end");

		$this->createAdd("carrier_graph_list", "CarrierGraphListComponent", array(
			"list" => $this->calc($start, $end)
		));
	}
	
	function calc($start, $end){
		
		try{
			$results = $this->orderDao->executeQuery($this->buildSql(), array(":start" => $start, ":end" => $end));
			if(count($results) === 0) return array();
		}catch(Exception $e){
			return array();
		}
		
		$array = array(
			"pc" => array("occurrences" => 0),
			"mobile" => array("occurrences" => 0),
			"tablet" => array("occurrences"=> 0),
			"smartphone" => array("occurrences" => 0)
		);
		foreach($results as $result){
			$attrs = soy2_unserialize($result["attributes"]);
			if(!isset($attrs["order_check_carrier"]["value"])) continue;
			$agent = $attrs["order_check_carrier"]["value"];
			
			/**
			 * キャリア判定
			 */
			//DoCoMo MOVA
			if(preg_match("/^DoCoMo\/1.0/i", $agent)){
				$array["mobile"]["occurrences"]++;
			//DoCoMo FOMA
			}elseif(preg_match("/^DoCoMo\/2.0/i", $agent)){
				$array["mobile"]["occurrences"]++;	
			//SoftBank
			}elseif(preg_match("/^(J-PHONE|Vodafone|MOT-[CV]|SoftBank)/i", $agent)){
				$array["mobile"]["occurrences"]++;
			//au
			}elseif(preg_match("/^KDDI-/i", $agent) || preg_match("/UP\.Browser/i", $agent)){
				$array["mobile"]["occurrences"]++;
			//ここからタブレット
			}elseif(strpos($agent, "ipad") !== false){
				$array["tablet"]["occurrences"]++;
			}elseif(strpos($agent, "windows") !== false && strpos($agent, "touch") !== false){
				$array["tablet"]["occurrences"]++;
			}elseif(strpos($agent, "android") !== false && strpos($agent, "mobile") === false){
				$array["tablet"]["occurrences"]++;
			}elseif(strpos($agent, "firefox") !== false && strpos($agent, "tablet") !== false){
				$array["tablet"]["occurrences"]++;
			}elseif(strpos($agent, "kindle") !== false || strpos($agent, "silk") !== false){
				$array["tablet"]["occurrences"]++;
			}elseif(strpos($agent, "playbook") !== false){
				$array["tablet"]["occurrences"]++;

			//ここからスマホ
			}elseif(strpos($agent, "iphone") !== false){
				$array["smartphone"]["occurrences"]++;
			}elseif(strpos($agent, "ipod") !== false){
				$array["smartphone"]["occurrences"]++;
			}elseif(strpos($agent, "android") !== false && strpos($agent, "mobile") !== false){
				$array["smartphone"]["occurrences"]++;
			}elseif(strpos($agent, "windows") !== false && strpos($agent, "phone") !== false){
				$array["smartphone"]["occurrences"]++;
			}elseif(strpos($agent, "firefox") !== false && strpos($agent, "mobile") !== false){
				$array["smartphone"]["occurrences"]++;
			}elseif(strpos($agent, "blackberry") !== false){
				$array["smartphone"]["occurrences"]++;
			//PC
			}else{
				$array["pc"]["occurrences"]++;
			}
		}
		
		foreach($array as $key => $values){
			if($values["occurrences"] > 0){
				$r = rand(0,255);
				$g = rand(0,255);
				$b = rand(0,255);
				$a = "0.".rand(1,9);
				$array[$key]["color"] = "rgba(" . $r . ", " . $g . ", " . $b . ", " . $a . ")";
			}else{
				unset($array[$key]);
			}
		}
						
		return $array;
	}
	
	function buildSql(){
		$sql = "SELECT attributes ".
				"FROM soyshop_order ".
				"WHERE order_status > " . SOYShop_Order::ORDER_STATUS_INTERIM . " ".
				"AND order_status < " . SOYShop_Order::ORDER_STATUS_CANCELED . " ".
				"AND order_date >= :start ".
				"AND order_date <= :end";
		
		if(isset($_POST["AnalyticsPlugin"]["limit"]) && strlen($_POST["AnalyticsPlugin"]["limit"]) > 0 && is_numeric($_POST["AnalyticsPlugin"]["limit"])){
			$sql .= " LIMIT " . (int)$_POST["AnalyticsPlugin"]["limit"];
		}
		
		return $sql;
	}
}

class CarrierGraphListComponent extends HTMLList{
	
	protected function populateItem($entity, $key) {
		$this->addLabel("label", array(
			"text" => (isset($key)) ? $key : ""
		));
		
		$this->addLabel("color", array(
			"text" => (isset($entity["color"])) ? $entity["color"] : "#FFFFFF"
		));
				
		$this->addLabel("occurrences", array(
			"text" => (isset($entity["occurrences"])) ? $entity["occurrences"] : 0
		));
	}
}
?>