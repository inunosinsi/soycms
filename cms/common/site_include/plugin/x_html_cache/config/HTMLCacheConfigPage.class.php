<?php

class HTMLCacheConfigPage extends WebPage {

	private $pluginObj;

	function __construct(){
		SOY2::import("site_include.CMSPage");
		SOY2::import("site_include.CMSBlogPage");
		SOY2::import("site_include.plugin.x_html_cache.component.PageListComponent");
	}

	function doPost(){
		if(soy2_check_token()){
			$this->pluginObj->config_per_page = (isset($_POST["config_per_page"])) ? $_POST["config_per_page"] : array();
			$this->pluginObj->config_per_blog = (isset($_POST["config_per_blog"])) ? $_POST["config_per_blog"] : array();

			CMSUtil::notifyUpdate();
			CMSPlugin::savePluginConfig($this->pluginObj->getId(), $this->pluginObj);
			CMSPlugin::redirectConfigPage();
		}
	}

	function execute(){
		parent::__construct();

		$this->addLabel("page_controller_path", array(
			"text" => UserInfoUtil::getSiteDirectory() . "index.php"
		));

		$this->addForm("form");

		//挿入するページの指定
		$this->createAdd("page_list","PageListComponent",array(
			"list"  => self::getPages(),
			"pluginObj" => $this->pluginObj
		));

		$this->addLabel("job_path", array(
			"text" => dirname(dirname(__FILE__)) . "/job/clear.php"
		));
	}

	function getPluginObj() {
		return $this->pluginObj;
	}
	function setPluginObj($pluginObj) {
		$this->pluginObj = $pluginObj;
	}

	private function getPages(){
    	$result = SOY2ActionFactory::createInstance("Page.PageListAction",array(
    		"offset" => 0,
    		"count"  => 1000,
    		"order"  => "cdate"
    	))->run();

    	return $result->getAttribute("PageList");// + $result->getAttribute("RemovedPageList");
	}
}
