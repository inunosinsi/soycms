<?php
/**
 * @entity SOYShop_DataSets
 */
abstract class SOYShop_DataSetsDAO extends SOY2DAO{
	
	abstract function insert(SOYShop_DataSets $bean);
	
	/**
	 * @return object
	 * @query class_name = :class
	 */
	abstract function getByClass($class);
	
	/**
	 * @sql delete from soyshop_data_sets where class_name = :class
	 */
	abstract function clear($class);
	
	abstract function get();
}
?>