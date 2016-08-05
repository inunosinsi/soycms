<?php
/**
 * @entity SOYLpo_List
 */
abstract class SOYLpo_ListDAO extends SOY2DAO{

	abstract function insert(SOYLpo_List $bean);

	abstract function update(SOYLpo_List $bean);
	
	/**
	 * @return object
	 */
	abstract function getById($id);
	
	/**
	 * @return list
	 * @order id desc
	 */
	abstract function get();
	
	/**
	 * @return list
	 */
	abstract function getByMode($mode);
	
	abstract function deleteById($id);
	
	/**
	 * @return column_count
	 * @columns count(id) as count
	 */
	abstract function count();
	
	/**
	 * @return column_count
	 * @columns count(id) as count
	 */
	abstract function countByMode($mode);
}
?>