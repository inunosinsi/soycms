<?php
SOY2::import("domain.SOYInquiry_DataSets");
/**
 * @entity SOYInquiry_DataSets
 */
abstract class SOYInquiry_DataSetsDAO extends SOY2DAO{

	abstract function insert(SOYInquiry_DataSets $bean);

	/**
	 * @return object
	 * @query class_name = :class
	 */
	abstract function getByClass($class);

	/**
	 * @sql delete from soyinquiry_data_sets where class_name = :class
	 */
	abstract function clear($class);

	abstract function get();
}
