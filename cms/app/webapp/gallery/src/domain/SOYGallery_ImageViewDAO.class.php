<?php
/**
 * @entity SOYGallery_ImageView
 */
abstract class SOYGallery_ImageViewDAO extends SOY2DAO{

	/**
	 * @return id
	 */
	abstract function insert(SOYGallery_ImageView $bean);
	
	/**
	 * @return id
	 */
	abstract function update(SOYGallery_ImageView $bean);
	
	abstract function get();
	
	abstract function deleteById($id);
	
	/**
	 * @return object
	 */
	abstract function getById($id);
	
	/**
	 * @return list
	 * @query g_id = :gId
	 * @order sort ASC, create_date DESC
	 */
	abstract function getByGId($gId);
	
	/**
	 * @return list
	 * @query gallery_id = :galleryId AND is_public = 1
	 * @order sort ASC, create_date DESC
	 */
	abstract function getByGalleryIdAndIsPublic($galleryId);
	
	/**
	 * @return column_count
	 * @columns count(id) as count
	 * @query gallery_id = :galleryId AND is_public = 1
	 *
	 */
	abstract function countIsPublic($galleryId);
	
	
}
?>