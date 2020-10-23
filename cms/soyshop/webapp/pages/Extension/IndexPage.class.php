<?php

class IndexPage extends WebPage{

	private $title;

	function doPost(){}

	function __construct($args){
		$pluginId = (isset($args[0])) ? $args[0] : null;
		$plugin = SOYShopPluginUtil::getPluginById($pluginId);
		if(is_null($plugin->getId())) SOY2PageController::jump("");

		parent::__construct();

		SOYShopPlugin::load("soyshop.admin.list");
		$exts = self::delegate($pluginId)->getContents();

		if(!isset($exts[$pluginId])) SOY2PageController::jump("");
		$ext = $exts[$pluginId];
		$this->title = (isset($ext["title"])) ? $ext["title"] : null;

		$this->addLabel("page_content", array(
			"html" => (isset($ext["content"])) ? $ext["content"] : null
		));
	}

	function getBreadcrumb(){
		return BreadcrumbComponent::build($this->title);
	}

	function getScripts(){
		$scripts = self::delegate()->getScripts();
		return (isset($scripts) && is_array($scripts)) ? $scripts : array();
	}

	function getCSS(){
		$css = self::delegate()->getCSS();
		return (isset($css) && is_array($css)) ? $css : array();
	}

	private function delegate($pluginId = null){
		static $delegate;
		if(is_null($delegate)){
			$delegate = SOYShopPlugin::invoke("soyshop.admin.list", array(
				"mode" => "list",
				"pluginId" => $pluginId
			));
		}
		return $delegate;
	}
}
