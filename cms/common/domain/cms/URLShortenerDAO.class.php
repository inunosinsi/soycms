<?php

/**
 * @entity cms.URLShortener
 */
abstract class URLShortenerDAO extends SOY2DAO{
	
	/**
	 * @return id
	 * @trigger onInsert
	 */
	abstract function insert(URLShortener $bean);
	
	/**
	 * @trigger onUpdate
	 */
	abstract function update(URLShortener $bean);
	
	abstract function delete($id);
	
	/**
	 * @return object
	 */
	abstract function getById($id);
	
	abstract function get();
	
	/**
	 * @return object
	 */
	abstract function getByFrom($from);

	/**
	 * @return object
	 * @query #targetType# = :targetType AND #targetId# = :targetId
	 */
	abstract function getByTargetTypeANDTargetId($targetType, $targetId);

	
	/**
	 * @final
	 */
	function onInsert($query,$binds){
		$binds[':cdate'] = time();
		$binds[':udate'] = time();
		return array($query,$binds);
	}

	/**
	 * @final
	 */
	function onUpdate($query,$binds){
		$binds[':udate'] = time();
		return array($query,$binds);
	}
	
	
	

}
?>