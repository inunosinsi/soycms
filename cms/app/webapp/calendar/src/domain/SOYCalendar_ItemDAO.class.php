<?php
/**
 * @entity SOYCalendar_Item
 */
abstract class SOYCalendar_ItemDAO extends SOY2DAO{

	/**
	 * @return id
	 * @trigger onInsert
	 */
	abstract function insert(SOYCalendar_Item $bean);
	
	/**
	 * @trigger onUpdate
	 */
	abstract function update(SOYCalendar_Item $bean);
	
	/**
	 * @return list
	 * @order id desc
	 */
	abstract function get();
	
	abstract function deleteById($id);
	
	/**
	 * @query id = :id AND title_id = :titleId
	 */
	abstract function deleteByIdAndTitleId($id,$titleId);
	
	/**
	 * @return object
	 */
	abstract function getById($id);
	
	/**
	 * @return list
	 * @order title_id asc
	 */
	abstract function getByScheduleDate($scheduleDate);
	
	/**
	 * @final
	 * @parma int<timestamp>, int<timestamp>
	 * @return array(SOYCalendar_Item...)
	 */
	function getItemsFromFirstDateToLastDate(int $first, int $last){
		$sql = "SELECT * FROM soycalendar_item ".
				"WHERE schedule_date >= " . $first . " ".
				"AND schedule_date <= " . $last;

		try{
			$res = $this->executeQuery($sql);
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return array();
		
		$arr = array();
		foreach($res as $v){
			$arr[] = $this->getObject($v);
		}

		return $arr;
	}

	/**
	 * @final
	 */
	function getFirstItemScheduleDateYear(){
		try{
			$res = $this->executeQuery("SELECT schedule_date FROM soycalendar_item WHERE schedule_date IS NOT NULL ORDER BY schedule_date ASC LIMIT 1;");
		}catch(Exception $e){
			$res = array();
		}
		
		return (isset($res[0]["schedule_date"])) ? (int)date("Y", $res[0]["schedule_date"]) : (int)date("Y");
	}

	/**
	 * @final
	 */
	function onInsert($query, $binds){
		$binds[":createDate"] = time();
		$binds[":updateDate"] = time();
		return array($query, $binds);
	}
	
	/**
	 * @final
	 */
	function onUpdate($query, $binds){
		$binds[":updateDate"] = time();
		return array($query, $binds);
	}
}
