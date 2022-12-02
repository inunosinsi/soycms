<?php
class Plugin{

	private $id;
	private $name;
	private $type;	//categoryの代わり
	private $description;
	private $isActive;
	private $config;
	private $custom;
	private $category;
	private $author;
	private $version;
	private $url;
	private $mail;

	// プラグインのtype
	const TYPE_ACTIVE = 	0;
	const TYPE_SOYCMS = 	1;
	const TYPE_ENTRY = 		2;
	const TYPE_LABEL = 		3;
	const TYPE_PAGE = 		4;
	const TYPE_BLOCK = 		5;
	const TYPE_IMAGE = 		6;
	const TYPE_SITE = 		7;
	const TYPE_EXTERNAL =	8;
	const TYPE_SOYAPP = 	9;
	const TYPE_DB = 		10;
	const TYPE_OPTIMIZE = 	11;

	public static function getPluginTypeList(){
		return array(
			self::TYPE_SOYCMS => "SOY CMS",
			self::TYPE_ENTRY => "記事",
			self::TYPE_LABEL => "ラベル",
			self::TYPE_PAGE => "ページ",
			self::TYPE_BLOCK => "ブロック",
			self::TYPE_IMAGE => "画像",
			self::TYPE_SITE => "サイト",
			self::TYPE_EXTERNAL => "外部サービス",
			self::TYPE_SOYAPP => "SOY App連携",
			self::TYPE_DB => "データベース",
			self::TYPE_OPTIMIZE => "最適化"
		);
	}

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

	/** categoryを廃止してSOY Shopと合わせてtypeにする */
	function getType(){
		return $this->type;
	}
	function setType($type){
		$this->type = $type;
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

		//プラグインのディレクトリ名とPLUGIN_IDが異なる場合
		$pluginDirName = CMSPlugin::getPluginDirectoryNameByPluginId($this->getId());
		if(file_exists(SOY2::RootDir()."site_include/plugin/".$pluginDirName."/icon.gif")){
			return SOY2PageController::createRelativeLink("../common/site_include/plugin/".$pluginDirName."/icon.gif");
		}
		
		return SOY2PageController::createRelativeLink("../soycms/image/icon/default_plugin_icon.png");
	}
}
