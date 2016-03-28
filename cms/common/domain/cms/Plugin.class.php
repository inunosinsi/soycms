<?php
class Plugin{
	
	private $id;
	private $name;
	private $description;
	private $isActive;
	private $config;
	private $custom;
	private $category;
	private $author;
	private $version;
	private $url;
	private $mail;
	
	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}
	function getName() {
		return $this->name;
	}
	function setName($name) {
		$this->name = $name;
	}
	function getDescription() {
		return $this->description;
	}
	function setDescription($description) {
		$this->description = $description;
	}
	function isActive() {
		if(is_null($this->isActive)){
			$this->isActive = CMSPlugin::activeCheck($this->id);
		}
		return $this->isActive;
	}
	function getIsActive() {
		return $this->isActive;
	}
	function setIsActive($isActive) {
		$this->isActive = $isActive;
	}
	function getConfig() {
		return $this->config;
	}
	function setConfig($config) {
		$this->config = $config;
	}
	function getCustom() {
		return $this->custom;
	}
	function setCustom($custom) {
		$this->custom = $custom;
	}
	
	function setCategory($category){
		
		$this->category = $category;
	}
	function getCategory(){
		if(is_null($this->category)){
			$dao = SOY2DAOFactory::create("cms.PluginDAO");
			$list = $dao->getCategoryArray();
//			$list = PluginDAO::getCategoryArray();
			foreach($list as $category => $ids){
				if(in_array($this->id,$ids)){
					$this->category = $category;
				}
			}
		}else{
		}
		return $this->category;
	}

	function getAuthor() {
		return $this->author;
	}
	function setAuthor($author) {
		$this->author = $author;
	}
	function getVersion() {
		return $this->version;
	}
	function setVersion($version) {
		$this->version = $version;
	}
	function getUrl() {
		return $this->url;
	}
	function setUrl($url) {
		$this->url = $url;
	}
	function getMail() {
		return $this->mail;
	}
	function setMail($mail) {
		$this->mail = $mail;
	}
	
	function getIcon(){
		//アイコン設定
		$prefix =  SOY2PageController::createRelativeLink("../common/site_include/plugin/".$this->getId()."/icon");
		
		$dir = SOY2::RootDir()."site_include/plugin/".$this->getId()."/icon";
		
		if(file_exists($dir.".jpg")){
			return $prefix.".jpg";
		}else if(file_exists($dir.".png")){
			return $prefix.".png";
		}else if(file_exists($dir.".gif")){
			return $prefix.".gif";
		}		
		
		return SOY2PageController::createRelativeLink("./image/icon/default_plugin_icon.png");
	}
}
?>
