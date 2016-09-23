<?php

class MergeSitemapConfigFormPage extends WebPage{
	
	private $pluginObj;
	
	function __construct(){}
	
	function doPost(){

		if(soy2_check_token() && strlen($_POST["sitemaps"])){
			$this->pluginObj->setUrls(explode("\n", $_POST["sitemaps"]));
			
			CMSPlugin::savePluginConfig(MergeSitemapPlugin::PLUGIN_ID,$this->pluginObj);
			CMSPlugin::redirectConfigPage();
		}
	}
	
	function execute(){
		if(method_exists("WebPage", "WebPage")){
			WebPage::WebPage();
		}else{
			WebPage::__construct();
		}
		
		$this->addLabel("xml_file_path", array(
			"text" => SOY2Logic::createInstance("site_include.plugin.merge_sitemap.logic.MergeSitemapLogic")->getMergeXMLFilePath()
		));
		
		$this->addForm("form");
		
		$this->addTextArea("sitemaps", array(
			"name" => "sitemaps",
			"value" => trim(implode("\n", $this->pluginObj->getUrls())),
			"style" => "width:90%;"
		));
	}
		
	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}