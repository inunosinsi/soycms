<?php
/**
 * @table soyvoice_config
 */
class SOYVoice_Config {

    /**
	 * @id
	 */
	private $id;
	
	/**
	 * @column owner_name
	 */
	private $ownerName;
	
	/**
	 * @column owner_display
	 */
	private $ownerDisplay;
	private $count;
	private $archive;
	private $resize;
	
	/**
	 * @column is_resize
	 */
	private $isResize;
	
	/**
	 * @column sync_site
	 */
	private $syncSite;
	private $label;
	
	/**
	 * @column is_sync
	 */
	private $isSync;
	
	/**
	 * @column is_published
	 */
	private $isPublished;
	private $config;
	
	function getId(){
		return $this->id;
	}
	function setId($id){
		$this->id = $id;
	}
	
	function getOwnerName(){
		return $this->ownerName;
	}
	function setOwnerName($ownerName){
		$this->ownerName = $ownerName;
	}
	
	function getOwnerDisplay(){
		return $this->ownerDisplay;
	}
	function setOwnerDisplay($ownerDisplay){
		$this->ownerDisplay = $ownerDisplay;
	}
	
	function getCount(){
		return $this->count;
	}
	function setCount($count){
		$this->count = $count;
	}
	
	function getArchive(){
		return $this->archive;
	}
	function setArchive($archive){
		$this->archive = $archive;
	}
	
	function getResize(){
		return $this->resize;
	}
	function setResize($resize){
		$this->resize = $resize;
	}
	
	function getIsResize(){
		return $this->isResize;
	}
	function setIsResize($isResize){
		$this->isResize = $isResize;
	}
	
	function getSyncSite(){
		return $this->syncSite;
	}
	function setSyncSite($syncSite){
		$this->syncSite = $syncSite;
	}
	
	function getLabel(){
		return $this->label;
	}
	function setLabel($label){
		$this->label = $label;
	}
	
	function getIsSync(){
		return $this->isSync;
	}
	function setIsSync($isSync){
		$this->isSync = $isSync;
	}
	
	function getIsPublished(){
		return $this->isPublished;
	}
	function setIsPublished($isPublished){
		$this->isPublished = $isPublished;
	}
	
	function getConfig(){
		return $this->config;
	}
	function setConfig($config){
		$this->config = $config;
	}
}
?>