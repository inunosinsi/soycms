<?php
/**
 * フィード表示プラグイン
 *
 */
SOYListConnectorPlugin::register();

class SOYListConnectorPlugin{
	
	const PLUGIN_ID = "soylist_connector";
	
	function getId(){
		return SOYListConnectorPlugin::PLUGIN_ID;
	}
	
	/**
	 * 初期化
	 */
	function init(){
		
		CMSPlugin::addPluginMenu($this->getId(),array(
			"name"=>"SOY List連携プラグイン",
			"description"=>"SOY Listでカテゴリ分け表示を行えるようにする",
			"author"=>"日本情報化農業研究所",
			"url"=>"http://www.n-i-agroinformatics.com/",
			"mail"=>"soycms@soycms.net",
			"version"=>"0.5"
		));	
		CMSPlugin::addPluginConfigPage($this->getId(),array(
			$this,"config_page"
		));
		
		if(CMSPlugin::activeCheck($this->getId())){
			CMSPlugin::setEvent('onPageOutput',$this->getId(),array($this,"onPageOutput"));
		}
	}
	
	function onPageOutput($obj){
		
		$obj->createAdd("soylist_plugin","SOYListComponent",array(
			"soy2prefix" => "app",
		));
	}
		
	/**
	 * 設定画面の表示
	 */
	function config_page(){
		include(dirname(__FILE__)."/config.php");
		$form = SOY2HTMLFactory::createInstance("SOYListConnectorConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}	
	
	public static function register(){
		
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj){
			$obj = new SOYListConnectorPlugin();
		}
			
		CMSPlugin::addPlugin(self::PLUGIN_ID,array($obj,"init"));
	}
	
}

class SOYListComponent extends SOYBodyComponentBase{
	
	function execute(){
		$execute = $this->getAttribute("app:execute");
		if(!isset($execute))return;
		
		if(!defined("SOY_LIST_IMAGE_ACCESS_PATH"))define("SOY_LIST_IMAGE_ACCESS_PATH", "/listImage/");
		
		include_once(dirname(__FILE__)."/class/common.php");
		$old = SOYListCommon::setConfig();
		
		$this->createAdd("list","SOYListAllListComponent",array(
			"soy2prefix" => "cms",
			"list" => $this->getCategories(),
			"checked" => $this->getList(),
			"itemDao" => SOY2DAOFactory::create("SOYList_ItemDAO")
		));

		parent::execute();
		
		SOYListCommon::resetConfig($old);
	}
	
	function getList(){
		$listDao = SOY2DAOFactory::create("SOYList_ListDAO");
		$array = $listDao->get();
 		$list = $array->getConfig();
 		return $this->convertList($list);
	}
	
	function convertList($array){
		foreach($array as $key => $value){
			if(!is_numeric($key)){
				unset($array[$key]);
			}
		}
		return $array;
	}
	
	function getCategories(){
		$categoryDao = SOY2DAOFactory::create("SOYList_CategoryDAO");
		  	
		try{
			$categories = $categoryDao->get();
		}catch(Exception $e){
			$categories = array();
		}
		return $categories;
	}
}

class SOYListAllListComponent extends HTMLList{
	
	private $checked;
	private $itemDao;
	
	protected function populateItem($entity){
		$prefix = "cms";
		
		$this->addLabel("category_name",array(
			"soy2prefix" => $prefix,
			"text" => $entity->getName()
		));
		
		$this->addLabel("category_memo",array(
			"soy2prefix" => $prefix,
			"text" => $entity->getMemo()
		));		
				
		$this->createAdd("item_list","SOYListPluginItemListComponent",array(
			"soy2prefix" => $prefix,
			"list" => $this->itemDao->getByCategoryIdAndChecked($entity->getId(),$this->checked),
			"categoryName" => $entity->getName()
		));
	}
	
	function setChecked($checked){
		$this->checked = $checked;
	}
	function setItemDao($itemDao){
		$this->itemDao = $itemDao;
	}
}

class SOYListPluginItemListComponent extends HTMLList{
	
	private $categoryName;
	
	protected function populateItem($entity){
		$prefix = "cms";
		
		$this->createAdd("id","HTMLLabel",array(
			"text" => $entity->getId(),
			"soy2prefix" => $prefix
		));
		
		$this->createAdd("name","HTMLLabel",array(
			"text" => $entity->getName(),
			"soy2prefix" => $prefix
		));
		
		$this->createAdd("category","HTMLLabel",array(
			"text" => $this->categoryName,
			"soy2prefix" => $prefix
		));
		
		$this->createAdd("image","HTMLImage",array(
			"src" => SOY_LIST_IMAGE_ACCESS_PATH . $entity->getImage(),
			"alt" => htmlspecialchars($entity->getName(),ENT_QUOTES),
			"soy2prefix" => $prefix
		));
		
		$this->createAdd("price","HTMLLabel",array(
			"text" => number_format($entity->getPrice()),
			"soy2prefix" => $prefix
		));
		
		$this->createAdd("standard","HTMLLabel",array(
			"text" => $entity->getStandard(),
			"soy2prefix" => $prefix
		));
		
		$this->createAdd("description","HTMLLabel",array(
			"html" => nl2br(htmlspecialchars($entity->getDescription(),ENT_QUOTES)),
			"soy2prefix" => $prefix
		));
		
		$this->createAdd("url","HTMLLink",array(
			"link" => htmlspecialchars($entity->getUrl(),ENT_QUOTES),
			"soy2prefix" => $prefix
		));
		
		$this->createAdd("create_date","DateLabel",array(
			"text" => $entity->getCreateDate(),
			"soy2prefix" => $prefix
		));
		
		$this->createAdd("update_date","DateLabel",array(
			"text" => $entity->getUpdateDate(),
			"soy2prefix" => $prefix
		));
	}
	
	function setCategoryName($categoryName){
		$this->categoryName = $categoryName;
	}
}
?>