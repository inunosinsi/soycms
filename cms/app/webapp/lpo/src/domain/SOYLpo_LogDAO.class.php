<?php
/**
 * @entity SOYLpo_Log
 */
abstract class SOYLpo_LogDAO extends SOY2DAO{

	abstract function insert(SOYLpo_Log $bean);
	
	/**
	 * @return list
	 * @order id desc
	 */
	abstract function get();
	
	/**
	 * @return column_count
	 * @columns count(*) as count
	 * @group lpo_id
	 */
	abstract function countLpoId();
	
}
?>