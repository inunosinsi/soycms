<?php
/**
 * @table soygallery_gallery
 */
class SOYGallery_Gallery {

	/**
	 * @id
	 */
	private $id;

	/**
	 * @column gallery_id
	 */
	private $galleryId;
	private $name;
	private $memo;
	private $config;

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

	function getName(){
		return $this->name;
	}
	function setName($name){
		$this->name = $name;
	}

	function getMemo(){
		return $this->memo;
	}
	function setMemo($memo){
		$this->memo = $memo;
	}

	function getConfig(){
		return $this->config;
	}
	function setConfig($config){
		$this->config = $config;
	}

	function getConfigArray(){
		return soy2_unserialize($this->getConfig());
	}
	function setConfigArray($config){
		$this->setConfig(soy2_serialize($config));
	}

	function getConfigValue($key){
		$configs = soy2_unserialize($this->config);
		return (isset($configs[$key])) ? $configs[$key] : "";
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
}
