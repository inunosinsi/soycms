<?php
/**
 * @entity SOYCalendar_Item
 */
abstract class SOYCalendar_ItemDAO extends SOY2DAO{

	/**
	 * @return id
	 */
	abstract function insert(SOYCalendar_Item $bean);
	
	abstract function update(SOYCalendar_Item $bean);
	
	/**
	 * @return list
	 * @order id desc
	 */
	abstract function get();
	
	abstract function deleteById($id);
	
	/**
	 * @query id = :id AND title = :title
	 */
	abstract function deleteByIdAndTitle($id,$title);
	
	/**
	 * @return object
	 */
	abstract function getById($id);
	
	/**
	 * @return list
	 * @order title asc
	 */
	abstract function getBySchedule($schedule);
}
?>