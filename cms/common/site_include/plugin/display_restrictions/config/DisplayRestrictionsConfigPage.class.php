<?php
class DisplayRestrictionsConfigPage extends WebPage{

	private $pluginObj;

	function __construct(){
		//挿入するページの指定
		SOY2::import('site_include.CMSPage');
		SOY2::import('site_include.CMSBlogPage');
	}

	function doPost(){
		$this->pluginObj->config_per_page = (isset($_POST["config_per_page"])) ? $_POST["config_per_page"] : array();

		CMSPlugin::savePluginConfig(DisplayRestrictionsPlugin::PLUGIN_ID, $this->pluginObj);
		CMSPlugin::redirectConfigPage();
	}

	function execute(){
		parent::__construct();

		$this->addForm("form");

		$this->createAdd("page_list","PageList",array(
			"list"  => self::_getPages(),
			"pluginObj" => $this->pluginObj
		));
	}

	function setPluginObj($pluginObj) {
		$this->pluginObj = $pluginObj;
	}

	private function _getPages(){
    	$result = SOY2ActionFactory::createInstance("Page.PageListAction",array(
    		"offset" => 0,
    		"count"  => 1000,
    		"order"  => "cdate"
    	))->run();

    	$list = $result->getAttribute("PageList");// + $result->getAttribute("RemovedPageList");

    	return $list;

	}
}

class PageList extends HTMLList{

	private $pluginObj;

	function populateItem($entity){
		$id = (is_numeric($entity->getId())) ? (int)$entity->getId() : 0;

		$this->addCheckBox("page_item", array(
			"type"     => "checkbox",
			"name"     => "config_per_page[".$id."]",
			"value"    => 1,
			"selected" => (isset($this->pluginObj->config_per_page[$id])),
			"label"    => $entity->getTitle() . " (/{$entity->getUri()})"
		));
	}


	function setPluginObj($pluginObj) {
		$this->pluginObj = $pluginObj;
	}
}
