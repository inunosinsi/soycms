<?php

class HTMLCacheConfigPage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.x_html_cache.util.HTMLCacheUtil");
	}

	function doPost(){

		if(soy2_check_token()){
			HTMLCacheUtil::savePageDisplayConfig($_POST["display_config"]);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$this->addLabel("page_controller_path", array(
			"text" => SOYSHOP_SITE_DIRECTORY . "index.php"
		));

		$this->addLabel("static_cache_execute_path", array(
			"text" => dirname(dirname(__FILE__)) . "/script/cache.php"
		));

		$this->addForm("form");

		SOY2::import("module.plugins.x_html_cache.component.PageListComponent");
		$this->createAdd("page_list", "PageListComponent", array(
			"list" => self::_getPageList(),
			"displayConfig" => HTMLCacheUtil::getPageDisplayConfig()
		));

		// $this->addImage("img_cart", array(
		// 	"src" => "/" . SOYSHOP_ID . "/themes/sample/soyshop_async_add_item.png"
		// ));
	}

	private function _getPageList(){
		try{
			$pages = SOY2DAOFactory::create("site.SOYShop_PageDAO")->get();
		}catch(Exception $e){
			return array();
		}

		$list = array();
		foreach($pages as $page){
			if(is_null($page->getId())) continue;
			$list[$page->getId()] = $page->getName();
		}

		return $list;
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
