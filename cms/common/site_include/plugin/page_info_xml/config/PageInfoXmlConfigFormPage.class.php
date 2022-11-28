<?php

class PageInfoXmlConfigFormPage extends WebPage{
	
	private $pluginObj;
	private $logic;
	private $xmlPath;
	
	function __construct(){
		$this->logic = SOY2Logic::createInstance("site_include.plugin.page_info_xml.logic.CreatePageInfoLogic");
		$this->xmlPath = $this->logic->getPageInfoXMLFilePath();
	}
	
	function doPost(){

		if(soy2_check_token() && strlen($_POST["sitemaps"])){
			//更新の度にXMLファイルは必ず削除
			if(file_exists($this->xmlPath)){
				unlink($this->xmlPath);
			}
			
			$this->pluginObj->setUrls(explode("\n", $_POST["sitemaps"]));
			$this->pluginObj->setRemoveStrings(explode("\n", $_POST["remove_strings"]));
			
			CMSPlugin::savePluginConfig(PageInfoXmlPlugin::PLUGIN_ID,$this->pluginObj);
			
			//XMLファイルを生成する
			$this->logic->createPageInfoXml($this->pluginObj->getUrls(), $this->pluginObj->getRemoveStrings());
			
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
			"text" => $this->xmlPath
		));
		
		DisplayPlugin::toggle("is_xml", file_exists($this->xmlPath));
		$this->addForm("form");
		
		$this->addTextArea("sitemaps", array(
			"name" => "sitemaps",
			"value" => trim(implode("\n", $this->pluginObj->getUrls())),
			"style" => "width:90%;height:100px;"
		));
		
		$this->addTextArea("remove_strings", array(
			"name" => "remove_strings",
			"value" => trim(implode("\n", $this->pluginObj->getRemoveStrings())),
			"style" => "width:90%;height:100px;"
		));
	}
		
	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}