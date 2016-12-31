<?php
define('APPLICATION_ID', "gallery");
/**
 * ページ表示
 */
class SOYGallery_PageApplication{

	private $page;
	private $serverConfig;
	

	function init(){
		CMSApplication::main(array($this, "main"));
		
		//DBの初期化を行う
		if(!file_exists(CMS_COMMON . "db/" . APPLICATION_ID . ".db")){
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
		if(!file_exists(CMS_COMMON . "db/" . APPLICATION_ID . ".db")){
			$logic = SOY2Logic::createInstance("logic.InitLogic");
			$logic->init();
		}
		
		$arguments = CMSApplication::getArguments();

		//app:id="soygallery"
		$this->page->createAdd("soygallery", "SOYGallery_ImageComponent", array(
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

class SOYGallery_ImageComponent extends SOYBodyComponentBase{
	
	private $page;
	private $application;
	
	
	function setPage($page){
		$this->page = $page;
	}
	
	function execute(){
		
		//ギャラリIDを取得
		$galleryId = trim($this->getAttribute("app:gallery"));
		
		$gallery = $this->getGallery($galleryId);
		
		$this->addLabel("gallery_name", array(
			"soy2prefix" => "block",
			"text" => $gallery->getName()
		));
		
		$this->addLabel("gallery_memo", array(
			"soy2prefix" => "block",
			"text" => $gallery->getMemo()
		));
		
		$this->buildGallery($galleryId, $gallery->getConfigArray());
		
		parent::execute();
	}
	
	function buildGallery($galleryId, $config){
		$args = (isset($this->page->arguments) && count($this->page->arguments) > 0) ? $this->page->arguments : array();
		$page = null;
		
		SOY2::import("logic.PagerLogic");
		
		$limit = (isset($config["count"]) && (int)$config["count"] > 0) ? (int)$config["count"] : 10;
		if(count($args) > 0) $page = (isset($args[0])) ? $args[0] : null;
		if(is_null($page)) $page = 1;
		$offset = ($page - 1) * $limit;
		
		$dao = SOY2DAOFactory::create("SOYGallery_ImageViewDAO");
		
		try{
			$total = $dao->countIsPublic($galleryId);
		}catch(Exception $e){
			$total = 0;
		}
		
		$dao->setLimit($limit);
		$dao->setOffset($offset);
		try{
			$images = $dao->getByGalleryIdAndIsPublic($galleryId);
		}catch(Exception $e){
			$images = array();
		}
		
		$this->createAdd("image_list", "GalleryComponent", array(
			"soy2prefix" => "block",
			"list" => $images
		));
		
		//ページャー
		$start = $offset;
		$end = $start + count($images);
		if($end > 0 && $start == 0) $start = 1;

		$pager = new PagerLogic();
		$pager->setPageUrl($this->page->getPageUrl());
		$pager->setPage($page);
		$pager->setStart($start);
		$pager->setEnd($end);
		$pager->setTotal($total);
		$pager->setLimit($limit);
		
		try{
			$this->buildPager($pager);
		}catch(Exception $e){
			//
		}
	}
	
	function buildPager(PagerLogic $pager){

		//件数情報表示
		$this->addLabel("count_start", array(
			"soy2prefix" => "cms",
			"text" => $pager->getStart()
		));
		$this->addLabel("count_end", array(
			"soy2prefix" => "cms",
			"text" => $pager->getEnd()
		));
		$this->addLabel("count_max", array(
			"soy2prefix" => "cms",
			"text" => $pager->getTotal()
		));

		//ページへのリンク
		$this->addLink("next_pager", $pager->getNextParam());
		$this->addLink("prev_pager", $pager->getPrevParam());
		$this->createAdd("pager_list", "SimplePager", $pager->getPagerParam());
		
		//ページへジャンプ
		$this->addForm("pager_jump", array(
			"soy2prefix" => "cms",
			"method" => "get",
			"action" => $pager->getPageURL()."/"
		));
		$this->addSelect("pager_select", array(
			"soy2prefix" => "cms",
			"name" => "page",
			"options" => $pager->getSelectArray(),
			"selected" => $pager->getPage(),
			"onchange" => "location.href=this.parentNode.action+this.options[this.selectedIndex].value"
		));
		
	}
	
	function getGallery($galleryId){
		$dao = SOY2DAOFactory::create("SOYGallery_GalleryDAO");
		try{
			$gallery = $dao->getByGalleryId($galleryId);
		}catch(Exception $e){
			$gallery = new SOYGallery_Gallery();
		}
		
		return $gallery;
	}
		
	function getApplication(){
		return $this->application;
	}
	
	function setApplication($application){
		$this->application = $application;
	}
}

class GalleryComponent extends HTMLList{
	
	protected function populateItem($entity, $index){
		
		$prefix = "cms";
				
		$this->addLabel("id", array(
			"soy2prefix" => $prefix,
			"text" => $entity->getId()
		));
		
		$this->addLabel("filename", array(
			"soy2prefix" => $prefix,
			"text" => $entity->getFilename()
		));
		
		$this->addLabel("thumbnail_filename", array(
			"soy2prefix" => $prefix,
			"text" => "t_".$entity->getFilename()
		));
		
		$imageDir = $entity->getConfigValue("uploadDir");
		if(!isset($imageDir) || !strlen($imageDir)) $imageDir = SOY_GALLERY_IMAGE_UPLOAD_DIR . $entity->getGalleryId();
		
		$imagePath = rtrim(str_replace($_SERVER["DOCUMENT_ROOT"], "", $imageDir) , "/") . "/";
		$attributes = $entity->getAttributeArray();
		
		$this->addLink("image_link", array(
			"soy2prefix" => $prefix,
			"link" => $imagePath . $entity->getFilename(),
			"attr:title" => (isset($attributes["alt"])) ? $attributes["alt"] : ""
		));
		
		$this->addImage("image", array(
			"soy2prefix" => $prefix,
			"src" => $imagePath . $entity->getFilename(),
			"attr:alt" => (isset($attributes["alt"])) ? $attributes["alt"] : ""
		));
		
		$this->addLabel("image_path", array(
			"soy2prefix" => $prefix,
			"text" => $imagePath . "t_" . $entity->getFilename()
		));
		
		$this->addLink("thumbnail_link", array(
			"soy2prefix" => $prefix,
			"link" => $imagePath . "t_" . $entity->getFilename(),
			"attr:title" => (isset($attributes["alt"])) ? $attributes["alt"] : ""
		));
		
		$this->addImage("thumbnail", array(
			"soy2prefix" => $prefix,
			"src" => $imagePath . "t_" . $entity->getFilename(),
			"attr:alt" => (isset($attributes["alt"])) ? $attributes["alt"] : ""
		));
		
		$this->addLabel("thumbnail_path", array(
			"soy2prefix" => $prefix,
			"text" => $imagePath . "t_" . $entity->getFilename()
		));
		
		//サイズを調べて、縦横どちらが長いかを調べる。正方形の場合はwidth
		$imageDir = SOY_GALLERY_IMAGE_UPLOAD_DIR . $entity->getGalleryId() . "/";
		$imageInfo = getimagesize($imageDir.$entity->getFilename());
		$imageType = ($imageInfo[1] > $imageInfo[0]) ? "height" : "width";
		
		$this->addLabel("image_type",array(
			"soy2prefix" => $prefix,
			"text" => $imageType
		));
		
		$this->addLabel("image_type_wamei",array(
			"soy2prefix" => $prefix,
			"text" => ($imageType==="width") ? "yoko" : "tate"
		));
		
		$this->addLabel("url",array(
			"soy2prefix" => $prefix,
			"text" => $entity->getUrl()
		));
		
		$this->addLink("url_link",array(
			"soy2prefix" => $prefix,
			"text" => $entity->getUrl(),
			"link" => $entity->getUrl()
		));
		
		$this->addLabel("memo", array(
			"soy2prefix" => $prefix,
			"html" => nl2br($entity->getMemo())
		));
		
		$this->addLabel("sort", array(
			"soy2prefix" => $prefix,
			"text" => $index + 1
		));
		
		if(!class_exists("DateLabel")) include_once(CMS_COMMON . "site_include/DateLabel.class.php");
		$this->createAdd("create_date", "DateLabel", array(
			"soy2prefix" => $prefix,
			"text" => $entity->getCreateDate()
		));
		
		$this->createAdd("update_date", "DateLabel", array(
			"soy2prefix" => $prefix,
			"text" => $entity->getUpdateDate()
		));
	}
}

$app = new SOYGallery_PageApplication();
$app->init();
?>