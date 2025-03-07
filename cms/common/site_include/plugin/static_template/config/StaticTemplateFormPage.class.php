<?php

class StaticTemplateFormPage extends WebPage{

	private $pluginObj;

	function __construct(){
		SOY2::import("site_include.plugin.static_template.util.StaticTemplateUtil");
	}

	function doPost(){
		CMSUtil::notifyUpdate();
		CMSPlugin::redirectConfigPage();
	}

	function execute(){
		parent::__construct();

		$dir = StaticTemplateUtil::getTemplateDirectory();
		$this->addLabel("template_dir", array(
			"text" => $dir
		));

		$fs = StaticTemplateUtil::getTemplateFileNameList();

		DisplayPlugin::toggle("files", count($fs));

		SOY2::import("site_include.plugin.static_template.component.StaticTemplateListComponent");
		$this->createAdd("file_list", "StaticTemplateListComponent", array(
			"list" => $fs,
			"dir" => $dir	
		));
	}
	
	function setPluginObj(StaticTemplatePlugin $pluginObj) {
		$this->pluginObj = $pluginObj;
	}
}
