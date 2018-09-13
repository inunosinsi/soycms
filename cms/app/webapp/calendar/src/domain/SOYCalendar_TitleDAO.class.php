<?php
/**
 * @entity SOYCalendar_Title
 */
abstract class SOYCalendar_TitleDAO extends SOY2DAO{

    /**
	 * @return id
	 * @trigger onInsert
	 */
	abstract function insert(SOYCalendar_Title $bean);
	
	/**
	 * @trigger onUpdate
	 */
	abstract function update(SOYCalendar_Title $bean);
	
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
?>