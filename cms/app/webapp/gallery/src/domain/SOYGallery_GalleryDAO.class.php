<?php
/**
 * @entity SOYGallery_Gallery
 */
abstract class SOYGallery_GalleryDAO extends SOY2DAO{

	/**
	 * @return id
	 * @trigger onInsert
	 */
	abstract function insert(SOYGallery_Gallery $bean);
	
	/**
	 * @return id
	 * @trigger onUpdate
	 */
	abstract function update(SOYGallery_Gallery $bean);
	
	/**
	 * @return list
	 * @order id desc
	 */
	abstract function get();
	
	/**
	 * @return object
	 */
	abstract function getByGalleryId($galleryId);
	
	abstract function deleteById($id);
	
	/**
	 * @return object
	 */
	abstract function getById($id);
	
	/**
	 * @final
	 */
	function onInsert($query,$binds){
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