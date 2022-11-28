<?php
/**
 * @entity SOYCalendar_CustomItem
 */
abstract class SOYCalendar_CustomItemDAO extends SOY2DAO{

    /**
	 * @return id
	 * @trigger onInsert
	 */
	abstract function insert(SOYCalendar_CustomItem $bean);
	
	/**
	 * @trigger onUpdate
	 */
	abstract function update(SOYCalendar_CustomItem $bean);
	
	/**
	 * @return list
	 * @order id asc
	 */
	abstract function get();
	
	abstract function deleteById($id);
	
	/**
	 * @return object
	 */
	abstract function getById($id);

	/**
	 * @final
	 * @param array
	 * @return array
	 */
	function getClassListByIds(array $ids){
		if(!count($ids)) return array();

		try{
			$res = $this->executeQuery("SELECT id, alias FROM soycalendar_custom_item WHERE id IN (" . implode(",", $ids) . ")");
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return array();

		$arr = array();
		foreach($res as $v){
			$arr[$v["id"]] = htmlspecialchars($v["alias"], ENT_QUOTES, "UTF-8");
		}
		return $arr;
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