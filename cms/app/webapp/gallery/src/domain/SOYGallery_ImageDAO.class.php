<?php
/**
 * @entity SOYGallery_Image
 */
abstract class SOYGallery_ImageDAO extends SOY2DAO{

	/**
	 * @return id
	 * @trigger onInsert
	 */
	abstract function insert(SOYGallery_Image $bean);
	
	/**
	 * @return id
	 * @trigger onUpdate
	 */
	abstract function update(SOYGallery_Image $bean);
	
	/**
	 * @return list
	 * @order id desc
	 */
	abstract function get();
	
	abstract function deleteById($id);
	
	/**
	 * @return object
	 */
	abstract function getById($id);
	
	/**
	 * @final
	 */
	function onInsert($query,$binds){
		$binds[":memo"] = null;
		$binds[":sort"] = 99999;
		$binds[":isPublic"] = 1;
		$binds[":createDate"] = time();
		$binds[":updateDate"] = time();
		return array($query, $binds);
	}
	
	/**
	 * @final
	 */
	function onUpdate($query, $binds){
		$binds[":updateDate"] = time();
		return array($query, $binds);
	}
}
?>