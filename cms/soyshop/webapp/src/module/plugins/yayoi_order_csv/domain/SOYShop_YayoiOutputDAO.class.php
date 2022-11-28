<?php

/**
 * @entity SOYShop_YayoiOutput
 */
abstract class SOYShop_YayoiOutputDAO extends SOY2DAO{
	
	/**
	 * @trigger onInsert
	 */
	abstract function insert(SOYShop_YayoiOutput $bean);
	
	abstract function update(SOYShop_YayoiOutput $bean);
	
	/**
	 * @order create_date DESC
	 */
	abstract function get();
	
	function getExecutedOutputDate($year, $month){
		$start = mktime(0, 0, 0, $month, 1, $year);
		if($month === 12){
			$month = 1;
			$year++;
		}else{
			$month++;
		}
		$end = mktime(0, 0, 0, $month, 1, $year) - 1;
		
		$sql = "SELECT output_date FROM soyshop_yayoi_csv_output_date ".
				"WHERE output_date >= :start ".
				"AND output_date <= :end ".
				"GROUP BY output_date";
				
		try{
			$res = $this->executeQuery($sql, array(":start" => $start, ":end" => $end));
		}catch(Exception $e){
			return array();
		}
		
		if(!count($res)) return array();
		
		$list = array();
		foreach($res as $v){
			$list[] = (int)$v["output_date"];
		}
		
		return $list;
	}
	
	/**
	 * @final
	 */
	function onInsert($query, $binds){
		$binds[":createDate"] = time();
		return array($query, $binds);
	}
}
?>