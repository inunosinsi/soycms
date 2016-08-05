<?php
/**
 * @entity SOYList_Category
 */
abstract class SOYList_CategoryDAO extends SOY2DAO{

	/**
	 * @return id
	 * trigger onInsert
	 */
	abstract function insert(SOYList_Category $bean);
	
	/**
	 * @trigger onUpdate
	 */
	abstract function update(SOYList_Category $bean);
	
	/**
	 * @return list
	 * @order sort asc, id desc
	 */
	abstract function get();
	
	abstract function deleteById($id);
	
	/**
	 * @return object
	 */
	abstract function getById($id);
	
	/**
	 * @return object
	 */
	abstract function getByName($value);

	/**
	 * @final
	 */
	function onInsert($query,$binds){
		$binds[":sort"] = 100000;
		$binds[":createDate"] = time();
		$binds[":updateDate"] = time();
		
		return array($query,$binds);
	}
	
	/**
	 * @final
	 */
	function onUpdate($query,$binds){
		$binds[":updateDate"] = time();
		return array($query,$binds);
	}
}
?>