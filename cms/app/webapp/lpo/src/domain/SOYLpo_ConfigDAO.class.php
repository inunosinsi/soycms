<?php
/**
 * @entity SOYLpo_Config
 */
abstract class SOYLpo_ConfigDAO extends SOY2DAO{

	abstract function update(SOYLpo_Config $bean);
	
	/**
	 * @return Object
	 * @query id = 1
	 */
	abstract function get();
}
?>