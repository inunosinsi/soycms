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

		parent::__construct();

		SOYShopPlugin::load("soyshop.admin.list", $plugin);
		$exts = self::delegate()->getContents();

		if(!isset($exts[$pluginId])) SOY2PageController::jump("");
		$ext = $exts[$pluginId];

		$this->addLabel("page_name", array(
			"text" => (isset($ext["title"])) ? $ext["title"] : null
		));

		$this->addLabel("page_content", array(
			"html" => (isset($ext["content"])) ? $ext["content"] : null
		));
	}

	function getScripts(){
		$scripts = self::delegate()->getScripts();
		return (isset($scripts) && is_array($scripts)) ? $scripts : array();
	}

	function getCSS(){
		$css = self::delegate()->getCSS();
		return (isset($css) && is_array($css)) ? $css : array();
	}

	private function delegate(){
		static $delegate;
		if(is_null($delegate)){
			$delegate = SOYShopPlugin::invoke("soyshop.admin.list", array("mode" => "list"));
		}
		return $delegate;
	}
}
