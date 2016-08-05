<?php
/**
 * @table soygallery_image_view
 */
class SOYGallery_ImageView{
		
	/**
	 * @id
	 */
	private $id;
	private $filename;
	private $url;
	private $sort;
	private $memo;
	private $attributes;
	
	const NO_PUBLIC = 0;
	const IS_PUBLIC = 1;
	
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
	
	/**
	 * @column g_id
	 */
	private $gId;
	
	/**
	 * @column gallery_id
	 */
	private $galleryId;
	private $name;
	
	/**#@+
	 *
	 * @access public
	 */
	function getId(){
		return $this->id;
	}
	function setId($id){
		$this->id = $id;
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
	
	function getGId(){
		return $this->gId;
	}
	function setGId($gId){
		$this->gId = $gId;
	}
	
	function getGalleryId(){
		return $this->galleryId;
	}
	function setGalleryId($galleryId){
		$this->galleryId = $galleryId;
	}
	
	function getName(){
		return $this->name;
	}
	function setName($name){
		$this->name = $name;
	}
	/**#@-*/
	
	/** 便利メソッド **/
	function getAttributeArray(){
		$attributes = soy2_unserialize($this->attributes);
		if(!is_array($attributes))$attributes = array();
		return $attributes;
	}
}
?>