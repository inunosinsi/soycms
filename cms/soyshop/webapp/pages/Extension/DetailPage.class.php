<?php

class DetailPage extends WebPage{

	private $detailId;

	function doPost(){}

	function __construct($args){
		$pluginId = (isset($args[0])) ? $args[0] : null;
		$this->detailId = (isset($args[1])) ? $args[1] : null;
		try{
			$plugin = SOY2DAOFactory::create("plugin.SOYShop_PluginConfigDAO")->getByPluginId($pluginId);
		}catch(Exception $e){
			SOY2PageController::jump("");
		}

		parent::__construct();

		SOYShopPlugin::load("soyshop.admin.list", $plugin);
		$exts = SOYShopPlugin::invoke("soyshop.admin.list")->getContents();

		if(!isset($exts[$pluginId])) SOY2PageController::jump("");
		$ext = $exts[$pluginId];

		$this->addLink("list_link", array(
			"link" => SOY2PageController::createLink("Extension." . $pluginId),
			"text" => (isset($ext["title"])) ? $ext["title"] : null
		));

		//詳細用の拡張ポイント
		SOYShopPlugin::load("soyshop.admin.detail", $plugin);
		$detail = self::delegate($this->detailId)->getContent();

		$this->addLabel("page_name", array(
			"text" => (isset($detail["title"])) ? $detail["title"] : null
		));

		$this->addLabel("page_content", array(
			"html" => (isset($detail["content"])) ? $detail["content"] : null
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

	private function delegate($detailId = null){
		static $delegate;
		if(is_null($delegate)){
			$delegate = SOYShopPlugin::invoke("soyshop.admin.detail", array("detailId" => $detailId));
		}
		return $delegate;
	}
}
