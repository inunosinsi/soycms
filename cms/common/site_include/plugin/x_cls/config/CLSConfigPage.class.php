<?php

class CLSConfigPage extends WebPage {

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
			$this->pluginObj->setMode((int)$_POST["mode"]);
			$this->pluginObj->setMinWidth((int)$_POST["minWidth"]);
			$this->pluginObj->setResizeWidth((int)$_POST["resizeWidth"]);

			CMSUtil::notifyUpdate();
			CMSPlugin::savePluginConfig($this->pluginObj->getId(), $this->pluginObj);
			CMSPlugin::redirectConfigPage();
		}
	}

	function execute(){
		parent::__construct();

		$this->addForm("form");

		$this->addCheckBox("mode_property", array(
			"name" => "mode",
			"value" => CLSPlugin::MODE_PROPERTY,
			"selected" => $this->pluginObj->getMode() == CLSPlugin::MODE_PROPERTY,
			"label" => "<img>の属性値にwidthとheightを挿入",
			"onclick" => "toggleResizeConfig();"
		));

		$this->addCheckBox("mode_picture", array(
			"name" => "mode",
			"value" => CLSPlugin::MODE_PICTURE,
			"selected" => $this->pluginObj->getMode() == CLSPlugin::MODE_PICTURE,
			"label" => "<img>を<picture>タグで囲う",
			"onclick" => "toggleResizeConfig();"
		));

		$this->addInput("min_width", array(
			"name" => "minWidth",
			"value" => $this->pluginObj->getMinWidth(),
			"style" => "width:100px;"
		));

		$this->addInput("resize_width", array(
			"name" => "resizeWidth",
			"value" => $this->pluginObj->getResizeWidth(),
			"style" => "width:100px;"
		));

		$this->addLabel("resize_dir", array(
			"text" => $this->pluginObj->getResizeDir()
		));

		//挿入するページの指定
		$this->createAdd("page_list","PageListComponent",array(
			"list"  => self::getPages(),
			"pluginObj" => $this->pluginObj
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
