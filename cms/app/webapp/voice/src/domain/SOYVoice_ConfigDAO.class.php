<?php
/**
 * @entity SOYVoice_Config
 */
abstract class SOYVoice_ConfigDAO extends SOY2DAO{

   	/**
	 * @return id
	 */
	abstract function insert(SOYVoice_Config $bean);
	
	abstract function update(SOYVoice_Config $bean);
	
	/**
	 * @return list
	 * @order create_date desc
	 */
	abstract function get();
	
	/**
	 * @return object
	 */
	abstract function getById($id);
	
}
?>