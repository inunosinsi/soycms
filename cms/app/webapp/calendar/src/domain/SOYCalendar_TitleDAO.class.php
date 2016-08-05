<?php
/**
 * @entity SOYCalendar_Title
 */
abstract class SOYCalendar_TitleDAO extends SOY2DAO{

    /**
	 * @return id
	 */
	abstract function insert(SOYCalendar_Title $bean);
	
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
}
?>