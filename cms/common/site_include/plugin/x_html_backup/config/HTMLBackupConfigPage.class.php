<?php

class HTMLBackupConfigPage extends WebPage {

	private $pluginObj;

	function __construct(){
		if(!defined("PAGE_CHECKBOX_NO_ACTIVE")) define("PAGE_CHECKBOX_NO_ACTIVE", false);
		SOY2::import("site_include.plugin.x_html_cache.component.PageListComponent");
	}

	function doPost(){
		if(soy2_check_token()){
			if(isset($_POST["save"]) || isset($_POST["create"])){
				$this->pluginObj->config_per_page = (isset($_POST["config_per_page"])) ? $_POST["config_per_page"] : array();
				$this->pluginObj->config_per_blog = (isset($_POST["config_per_blog"])) ? $_POST["config_per_blog"] : array();

				CMSUtil::notifyUpdate();
				CMSPlugin::savePluginConfig($this->pluginObj->getId(), $this->pluginObj);

				if(isset($_POST["create"])){
					$logic = SOY2Logic::createInstance("site_include.plugin.x_html_backup.logic.HTMLBackupLogic");
					if(is_array($this->pluginObj->config_per_page)) $logic->setConfigPerPage($this->pluginObj->config_per_page);
					if(is_array($this->pluginObj->config_per_blog)) $logic->setConfigPerBlog($this->pluginObj->config_per_blog);
					$logic->generate();
					$logic->compress();
				}

				CMSPlugin::redirectConfigPage();
			}

			if(isset($_POST["download"])){
				SOY2Logic::createInstance("site_include.plugin.x_html_backup.logic.HTMLBackupLogic")->download();
			}
		}
	}

	function execute(){
		parent::__construct();

		$this->createAdd("page_list","PageListComponent",array(
			"list"  => self::_pages(),
			"pluginObj" => $this->pluginObj
		));

		$this->addForm("form");

		$logic = SOY2Logic::createInstance("site_include.plugin.x_html_backup.logic.HTMLBackupLogic");
		$zipFilePath = $logic->getBackupZipFilePath();
		DisplayPlugin::toggle("is_zip", file_exists($zipFilePath));
		DisplayPlugin::toggle("show_zip_path", file_exists($zipFilePath));

		$this->addForm("download_form");

		$this->addLink("download_link", array(
			"link" => $logic->getBackupZipFileUrl()
		));

		$this->addLabel("download_file_path", array(
			"text" => $zipFilePath
		));
	}

	private function _pages(){
    	return SOY2ActionFactory::createInstance("Page.PageListAction",array(
    		"offset" => 0,
    		"count"  => 1000,
    		"order"  => "cdate"
    	))->run()->getAttribute("PageList");;
	}

	function setPluginObj($pluginObj) {
		$this->pluginObj = $pluginObj;
	}
}