<?php

class IndexPage extends WebPage{

	private $title;

	function doPost(){}

	function __construct($args){
		$pluginId = (isset($args[0])) ? $args[0] : null;
		$plugin = soyshop_get_plugin_object($pluginId);
		if(is_null($plugin->getId())) SOY2PageController::jump("");

		parent::__construct();

		//詳細用の拡張ポイント
		SOYShopPlugin::load("soyshop.order.upload", $plugin);
		$detail = self::delegate($pluginId)->getContent();

		$this->title = (isset($detail["title"])) ? $detail["title"] : null;

		$this->addLabel("page_name", array(
			"text" => $this->title
		));

		$this->addLabel("page_content", array(
			"html" => (isset($detail["content"])) ? $detail["content"] : null
		));
	}

	function getBreadcrumb(){
		return BreadcrumbComponent::build($this->title, array("Order" => "注文管理"));
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
			$delegate = SOYShopPlugin::invoke("soyshop.order.upload", array("pluginId" => $pluginId));
		}
		return $delegate;
	}
}
