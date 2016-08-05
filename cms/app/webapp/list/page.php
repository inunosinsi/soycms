<?php
define('APPLICATION_ID', "list");
/**
 * ページ表示
 */
class SOYList_PageApplication{

	var $page;
	var $serverConfig;
	

	function init(){
		CMSApplication::main(array($this,"main"));
		
		//DBの初期化を行う
		if(!file_exists(CMS_COMMON . "db/".APPLICATION_ID.".db")){
			return;
		}
	}
	
	function prepare(){
		
	}

	function main($page){
		
		$this->page = $page;
		
		
		//SOY2::RootDir()の書き換え
		$oldRooDir = SOY2::RootDir();
		$oldPagDir = SOY2HTMLConfig::PageDir();
		$oldCacheDir = SOY2HTMLConfig::CacheDir();
		$oldDaoDir = SOY2DAOConfig::DaoDir();
		$oldEntityDir = SOY2DAOConfig::EntityDir();
		$oldDsn = SOY2DAOConfig::Dsn();
		$oldUser = SOY2DAOConfig::user();
		$oldPass = SOY2DAOConfig::pass();
		
		//設定ファイルの読み込み
		include_once(dirname(__FILE__) . "/config.php");
		$this->prepare();
		
		//DBの初期化を行う
		if(!file_exists(CMS_COMMON . "db/".APPLICATION_ID.".db")){
			$logic = SOY2Logic::createInstance("logic.InitLogic");
			$logic->init();
		}
		
		$arguments = CMSApplication::getArguments();

		//app:id="soylist"
		$this->page->createAdd("soylist","SOYList_ItemComponent",array(
			"application" => $this,
			"page" => $page,
			"soy2prefix" => "app"
		));
				
		//元に戻す
		SOY2::RootDir($oldRooDir);
		SOY2HTMLConfig::PageDir($oldPagDir);
		SOY2HTMLConfig::CacheDir($oldCacheDir);
		SOY2DAOConfig::DaoDir($oldDaoDir);
		SOY2DAOConfig::EntityDir($oldEntityDir);
		SOY2DAOConfig::Dsn($oldDsn);
		SOY2DAOConfig::user($oldUser);
		SOY2DAOConfig::pass($oldPass);

	}

}

class SOYList_ItemComponent extends SOYBodyComponentBase{
	
	private $page;
	private $application;
	
	
	function setPage($page){
		$this->page = $page;
	}
	
	function execute(){
	
		$this->getList();		
	
		parent::execute();
	}
	
	function getList(){
		$list = $this->getConfig();
		if(!is_array($list)) $list = array(); 
		
		if(count($list) > 0){
    		$update = $list["updateDate"];
    	}
    	
    	//配列で商品IDに関わるもの以外の値をすべて削除
    	$list = $this->convertList($list);
    	    	
    	$this->createAdd("update_date","DateLabel",array(
    		"text" => $update,
    		"soy2prefix" => "cms"
    	));
    	
    	$this->createAdd("item_list","ItemList",array(
    		"list" => $this->getItems($list),
    		"categoriesList" => $this->getCategories(),
    		"soy2prefix" => "block"
    	));
    	
    	$this->createAdd("no_list","HTMLModel",array(
    		"visible" => count($list) == 0,
    		"soy2prefix" => "block"
    	));
	}
	
	function getConfig(){
		$dao = SOY2DAOFactory::create("SOYList_ListDAO");
		$obj = $dao->get();
		return $obj->getConfig();	
	}
	
	function convertList($array){
		foreach($array as $key => $value){
			if(!is_numeric($key)){
				unset($array[$key]);
			}
		}
		return $array;
	}
	
	function getItems($ids){
    	$itemDao = SOY2DAOFactory::create("SOYList_ItemDAO");
    	try{
    		$items = $itemDao->getItemsByIds($ids);
    	}catch(Exception $e){
    		$items = array();
    	}
    	return $items;
    }
	
	function getCategories(){
    	$dao = SOY2DAOFactory::create("SOYList_CategoryDAO");
    	try{
    		$categories = $dao->get();
    	}catch(Exception $e){
    		$categories = array();
    	}
    	
    	$array = array();
    	
    	if(count($categories) > 0){
    		foreach($categories as $category){
	    		$array[$category->getId()] = $category->getName();
	    	}
    	}
    	
    	return $array;
    }

	
	function getApplication(){
		return $this->application;
	}
	
	function setApplication($application){
		$this->application = $application;
	}
	
}

class ItemList extends HTMLList{
	
	private $categoriesList;
		
	protected function populateItem($item){
		
		$this->createAdd("id","HTMLLabel",array(
			"text" => $item->getId(),
			"soy2prefix" => "cms"
		));
		
		$this->createAdd("name","HTMLLabel",array(
			"text" => $item->getName(),
			"soy2prefix" => "cms"
		));
		
		$this->createAdd("category","HTMLLabel",array(
			"text" => (isset($this->categoriesList[$item->getCategory()])) ? $this->categoriesList[$item->getCategory()] : "",
			"soy2prefix" => "cms"
		));
		
		$this->createAdd("image","HTMLImage",array(
			"src" => SOY_LIST_IMAGE_ACCESS_PATH . $item->getImage(),
			"alt" => htmlspecialchars($item->getName(),ENT_QUOTES),
			"soy2prefix" => "cms"
		));
		
		$this->createAdd("price","HTMLLabel",array(
			"text" => number_format($item->getPrice()),
			"soy2prefix" => "cms"
		));
		
		$this->createAdd("standard","HTMLLabel",array(
			"text" => $item->getStandard(),
			"soy2prefix" => "cms"
		));
		
		$this->createAdd("description","HTMLLabel",array(
			"html" => nl2br(htmlspecialchars($item->getDescription(),ENT_QUOTES)),
			"soy2prefix" => "cms"
		));
		
		$this->createAdd("url","HTMLLink",array(
			"link" => htmlspecialchars($item->getUrl(),ENT_QUOTES),
			"soy2prefix" => "cms"
		));
		
		$this->createAdd("create_date","DateLabel",array(
			"text" => $item->getCreateDate(),
			"soy2prefix" => "cms"
		));
		
		$this->createAdd("update_date","DateLabel",array(
			"text" => $item->getUpdateDate(),
			"soy2prefix" => "cms"
		));
	}
	
	function setCategoriesList($categoriesList){
		$this->categoriesList = $categoriesList;
	}
}

$app = new SOYList_PageApplication();
$app->init();

?>