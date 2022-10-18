<?php
SOY2::import("domain.SOYCalendar_DataSets");
/**
 * @entity SOYCalendar_DataSets
 */
abstract class SOYCalendar_DataSetsDAO extends SOY2DAO{

	abstract function insert(SOYCalendar_DataSets $bean);

	/**
	 * @return object
	 * @query class_name = :class
	 */
	abstract function getByClass($class);

	/**
	 * @sql delete from soycalendar_data_sets where class_name = :class
	 */
	abstract function clear($class);

	abstract function get();
}
