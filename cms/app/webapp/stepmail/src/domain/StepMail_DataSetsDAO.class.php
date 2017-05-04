<?php
/**
 * @entity StepMail_DataSets
 */
abstract class StepMail_DataSetsDAO extends SOY2DAO{
	
	/**
	 * @return id
	 */
	abstract function insert(StepMail_DataSets $bean);
	
	/**
	 * @return object
	 */
	abstract function getByClassName($className);
	
	/**
	 * @sql delete from stepmail_data_sets where class_name = :className
	 */
	abstract function clear($className);
}
?>