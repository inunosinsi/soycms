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
			"list" => soyshop_get_page_list(),
			"displayConfig" => HTMLCacheUtil::getPageDisplayConfig()
		));

		// $this->addImage("img_cart", array(
		// 	"src" => "/" . SOYSHOP_ID . "/themes/sample/soyshop_async_add_item.png"
		// ));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
