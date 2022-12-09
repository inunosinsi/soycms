<?php
SOY2::import('site_include.CMSPage');
class CMSApplicationPage extends CMSPage{

	var $error;

	function __construct($args) {

  		$id = $args[0];
		$this->arguments = $args[1];
		$this->siteConfig = $args[2];

		$this->page = soycms_get_application_page_object($id);
		$this->id = $id;

		$this->pageUrl = SOY2PageController::createLink("") . $this->page->getUri();

  		WebPage::__construct($args);

    }

    function main(){

    	$oldRooDir = SOY2::RootDir();
		$oldPagDir = SOY2HTMLConfig::PageDir();
		$oldCacheDir = SOY2HTMLConfig::CacheDir();
		$oldDaoDir = SOY2DAOConfig::DaoDir();
		$oldEntityDir = SOY2DAOConfig::EntityDir();
		$oldDsn = SOY2DAOConfig::Dsn();
		$oldUser = SOY2DAOConfig::user();
		$oldPass = SOY2DAOConfig::pass();

		//定数の作成
		define("CMS_APPLICATION_ROOT_DIR", dirname(SOY2::RootDir()) . "/app/");
		define("CMS_COMMON", SOY2::RootDir());
		if(!defined("SOYCMS_BLOG_PAGE_MODE")) define("SOYCMS_BLOG_PAGE_MODE", "_none_");

		include_once(CMS_APPLICATION_ROOT_DIR . "base/CMSApplication.class.php");

		$applicationId = $this->page->getApplicationId();

		//存在しなかったら何もしない
		if(!file_exists(CMS_APPLICATION_ROOT_DIR . "webapp/" . $applicationId . "/page.php")){
			return parent::main();
		}

		//読み込み
		include_once(CMS_APPLICATION_ROOT_DIR . "webapp/" . $applicationId . "/page.php");

    	try{
			//実行
			CMSApplication::page($this,$this->arguments);

	    	parent::main();
    	}catch(Exception $e){
    		SOY2::RootDir($oldRooDir);
			SOY2HTMLConfig::PageDir($oldPagDir);
			SOY2HTMLConfig::CacheDir($oldCacheDir);
			SOY2DAOConfig::DaoDir($oldDaoDir);
			SOY2DAOConfig::EntityDir($oldEntityDir);
			SOY2DAOConfig::Dsn($oldDsn);
			SOY2DAOConfig::user($oldUser);
			SOY2DAOConfig::pass($oldPass);

    		$this->error = $e;
    	}
    }

	function getError(){
		return $this->error;
	}
}
