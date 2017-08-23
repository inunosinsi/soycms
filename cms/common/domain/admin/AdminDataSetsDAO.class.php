<?php
/**
 * @entity AdminDataSets
 */
abstract class AdminDataSetsDAO extends SOY2DAO{

	abstract function insert(AdminDataSets $bean);

	/**
	 * @return object
	 * @query class_name = :class
	 */
	abstract function getByClass($class);

	/**
	 * @sql delete from soycms_admin_data_sets where class_name = :class
	 */
	abstract function clear($class);

	abstract function get();

}
?>