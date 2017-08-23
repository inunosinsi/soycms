<?php

class SitemapConfigFormPage extends WebPage{
	
	private $pluginObj;
	
	function __construct(){}
	
	function doPost(){
		if(isset($_POST["config_per_page"])){
			$this->pluginObj->config_per_page = $_POST["config_per_page"];
		}
		if(isset($_POST["config_per_blog"])){
			$this->pluginObj->config_per_blog = $_POST["config_per_blog"];
		}
		
		if(isset($_POST["ssl_per_page"])){
			$this->pluginObj->ssl_per_page = $_POST["ssl_per_page"];
		}
		if(isset($_POST["ssl_per_blog"])){
			$this->pluginObj->ssl_per_blog = $_POST["ssl_per_blog"];
		}


		CMSUtil::notifyUpdate();
		CMSPlugin::savePluginConfig(SitemapPlugin::PLUGIN_ID,$this->pluginObj);
		CMSPlugin::redirectConfigPage();

	}
	
	function execute(){
		parent::__construct();
		
		//挿入するページの指定
		SOY2::import('site_include.CMSPage');
		SOY2::import('site_include.CMSBlogPage');
		//SOY2HTMLFactory::importWebPage("CMSBlogPage");

		SOY2::import("site_include.plugin.sitemap.config.PageListComponent");
		$this->createAdd("page_list","PageListComponent",array(
			"list"  => self::getPages(),
			"pluginObj" => $this->pluginObj
		));
		
		SOY2::import("site_include.plugin.sitemap.config.SSLListComponent");
		$this->createAdd("ssl_list","SSLListComponent",array(
			"list"  => self::getPages(),
			"pluginObj" => $this->pluginObj
		));
	}
	
	private function getPages(){
    	$result = SOY2ActionFactory::createInstance("Page.PageListAction",array(
    		"offset" => 0,
    		"count"  => 1000,
    		"order"  => "cdate"
    	))->run();

    	$list = $result->getAttribute("PageList");// + $result->getAttribute("RemovedPageList");

    	return $list;

	}
	
	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
?>