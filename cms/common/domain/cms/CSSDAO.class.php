<?php

/**
 * @entity cms.CSS
 */
abstract class CSSDAO extends SOY2DAO{

	/**
	 * @return id
	 */
	abstract function insert(CSS $bean);
	
	abstract function delete($id);
	
	/**
	 * @return object
	 */
	abstract function getById($id);
	
	/**
	 * @index id
	 */
	abstract function get();
	
	/**
	 * @index id
	 * @column id,#filePath#
	 * @return array
	 */
	abstract function getCSSLists();
}
?>