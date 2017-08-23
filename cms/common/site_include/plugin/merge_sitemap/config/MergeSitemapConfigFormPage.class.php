<?php

class MergeSitemapConfigFormPage extends WebPage{
	
	private $pluginObj;
	private $logic;
	private $xmlPath;
	
	function __construct(){
		$this->logic = SOY2Logic::createInstance("site_include.plugin.merge_sitemap.logic.MergeSitemapLogic");
		$this->xmlPath = $this->logic->getMergeXMLFilePath();
	}
	
	function doPost(){

		if(soy2_check_token() && strlen($_POST["sitemaps"])){
			//更新の度にXMLファイルは必ず削除
			if(file_exists($this->xmlPath)){
				unlink($this->xmlPath);
			}
			
			$this->pluginObj->setUrls(explode("\n", $_POST["sitemaps"]));
			
			CMSPlugin::savePluginConfig(MergeSitemapPlugin::PLUGIN_ID,$this->pluginObj);
			
			//XMLファイルを生成する
			$this->logic->createMergeMap($this->pluginObj->getUrls());
			
			CMSPlugin::redirectConfigPage();
		}
	}
	
	function execute(){
		if(method_exists("WebPage", "WebPage")){
			WebPage::WebPage();
		}else{
			parent::__construct();
		}
		
		$this->addLabel("xml_file_path", array(
			"text" => $this->xmlPath
		));
		
		DisplayPlugin::toggle("is_xml", file_exists($this->xmlPath));
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