<?php
/**
 * @table soygallery_image
 */
class SOYGallery_Image {
	
	const TYPE_IMAGE = 0;
	const TYPE_THUMBNAIL = 1;
	
	const NO_PUBLIC = 0;
	const IS_PUBLIC = 1;
	
	/**
	 * @id
	 */
	private $id;
	
	/**
	 * @column gallery_id
	 */
	private $galleryId;
	private $filename;
	private $url;
	private $sort;
	private $memo;
	private $attributes;
	
	/**
	 * @column is_public
	 */
	private $isPublic;
	
	/**
	 * @column create_date
	 */
	private $createDate;
	
	/**
	 * @column update_date
	 */
	private $updateDate;
	
	function getId(){
		return $this->id;
	}
	function setId($id){
		$this->id = $id;
	}
	
	function getGalleryId(){
		return $this->galleryId;
	}
	function setGalleryId($galleryId){
		$this->galleryId = $galleryId;
	}
	
	function getFilename(){
		return $this->filename;
	}
	function setFilename($filename){
		$this->filename = $filename;
	}
	
	function getUrl(){
		return $this->url;
	}
	function setUrl($url){
		$this->url = $url;
	}
		
	function getSort(){
		return $this->sort;
	}
	function setSort($sort){
		$this->sort = $sort;
	}
	
	function getMemo(){
		return $this->memo;
	}
	function setMemo($memo){
		$this->memo = $memo;
	}
	
	function getAttributes(){
		return $this->attributes;
	}
	function setAttributes($attributes){
		$this->attributes = $attributes;
	}
	
	function getIsPublic(){
		return $this->isPublic;
	}
	function setIsPublic($isPublic){
		$this->isPublic = $isPublic;
	}
	
	function getCreateDate(){
		return $this->createDate;
	}
	function setCreateDate($createDate){
		$this->createDate = $createDate;
	}
	
	function getUpdateDate(){
		return $this->updateDate;
	}
	function setUpdateDate($updateDate){
		$this->updateDate = $updateDate;
	}
	
	
	/** 便利メソッド **/
	function getAttributeArray(){
		$attributes = soy2_unserialize($this->attributes);
		if(!is_array($attributes))$attributes = array();
		return $attributes;
	}
}
?>