<?php

class IndexPage extends WebPage{
	
	function doPost(){}
	
	function __construct($args){
		$pluginId = (isset($args[0])) ? $args[0] : null;
		try{
   			$plugin = SOY2DAOFactory::create("plugin.SOYShop_PluginConfigDAO")->getByPluginId($pluginId);
   		}catch(Exception $e){
			SOY2PageController::jump("");
   		}
		
		WebPage::__construct();
		
		SOYShopPlugin::load("soyshop.admin.list", $plugin);
		$exts = SOYShopPlugin::invoke("soyshop.admin.list")->getContents();
		
		if(!isset($exts[$pluginId])) SOY2PageController::jump("");
		$ext = $exts[$pluginId];
		
		$this->addLabel("page_name", array(
			"text" => (isset($ext["title"])) ? $ext["title"] : null
		));
		
		$this->addLabel("page_content", array(
			"html" => (isset($ext["content"])) ? $ext["content"] : null
		));
	}
}
?>