<?php
SOY2::import("domain.SOYMail_DataSets");
/**
 * @entity SOYMail_DataSets
 */
abstract class SOYMail_DataSetsDAO extends SOY2DAO{

	abstract function insert(SOYMail_DataSets $bean);

	/**
	 * @return object
	 * @query class_name = :class
	 */
	abstract function getByClass($class);

	/**
	 * @sql delete from soymail_data_sets where class_name = :class
	 */
	abstract function clear($class);

	abstract function get();
}
