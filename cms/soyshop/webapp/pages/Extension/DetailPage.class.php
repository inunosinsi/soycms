<?php

class DetailPage extends WebPage{

	private $parent;
	private $title;
	private $detailId;

	function doPost(){}

	function __construct($args){
		$pluginId = (isset($args[0])) ? $args[0] : null;
		$this->detailId = (isset($args[1])) ? $args[1] : null;

		$plugin = SOYShopPluginUtil::getPluginById($pluginId);
		if(is_null($plugin->getId())) SOY2PageController::jump("");

		parent::__construct();

		SOYShopPlugin::load("soyshop.admin.list", $plugin);
		$exts = SOYShopPlugin::invoke("soyshop.admin.list")->getContents();

		if(!isset($exts[$pluginId])) SOY2PageController::jump("");
		$ext = $exts[$pluginId];
		$this->parent["title"]= (isset($ext["title"])) ? $ext["title"] : null;
		$this->parent["link"] = "Extension." . $pluginId;

		//詳細用の拡張ポイント
		SOYShopPlugin::load("soyshop.admin.detail", $plugin);
		$detail = self::delegate($this->detailId)->getContent();
		$this->title = (isset($detail["title"])) ? $detail["title"] : null;

		$this->addLabel("page_content", array(
			"html" => (isset($detail["content"])) ? $detail["content"] : null
		));
	}

	function getBreadcrumb(){
		return BreadcrumbComponent::build($this->title, array($this->parent["link"] => $this->parent["title"]));
	}

	function getScripts(){
		$scripts = self::delegate()->getScripts();
		return (isset($scripts) && is_array($scripts)) ? $scripts : array();
	}

	function getCSS(){
		$css = self::delegate()->getCSS();
		return (isset($css) && is_array($css)) ? $css : array();
	}

	private function delegate($detailId = null){
		static $delegate;
		if(is_null($delegate)){
			$delegate = SOYShopPlugin::invoke("soyshop.admin.detail", array("detailId" => $detailId));
		}
		return $delegate;
	}
}
