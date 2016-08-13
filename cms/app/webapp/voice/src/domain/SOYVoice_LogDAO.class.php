<?php
/**
 * @entity SOYVoice_Log
 */
abstract class SOYVoice_LogDAO extends SOY2DAO{

	/**
	 * @return id
	 */
	abstract function insert(SOYVoice_Log $bean);
	
	abstract function update(SOYVoice_Log $bean);
	
	/**
	 * @return list
	 * @order id desc limit 1
	 */
	abstract function get();
}
?>