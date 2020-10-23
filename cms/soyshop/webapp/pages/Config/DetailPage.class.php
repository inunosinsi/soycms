<?php
/**
 * @class Config.DetailPage
 * @date 2009-07-28T16:04:26+09:00
 * @author SOY2HTMLFactory
 */
class DetailPage extends WebPage{

	private $title;

	function __construct(){
		if(!isset($_GET["plugin"])) SOY2PageController::jump("Config");

		$logic = SOY2Logic::createInstance("logic.plugin.SOYShopPluginLogic");
		$plugin = soyshop_get_plugin_object($_GET["plugin"]);
		if(is_null($plugin->getId())) SOY2PageController::jump("Config");

		parent::__construct();

		SOYShopPlugin::load("soyshop.config",$plugin);
		$delegate = SOYShopPlugin::invoke("soyshop.config", array(
			"mode" => "config"
		));

		$this->addLink("plugin_detail_link", array(
			"link" => SOY2PageController::createLink("Plugin.Detail." . $plugin->getId())
		));

		$this->title = $delegate->getTitle();

		$this->addLabel("plugin_title", array(
			"text" => $this->title
		));

		$this->addLabel("plugin_config", array(
			"html" => $delegate->getConfigPage()
		));
	}

	function getBreadcrumb(){
		return BreadcrumbComponent::build($this->title, array("Config" => "設定"));
	}

	function getScripts(){
		$root = SOY2PageController::createRelativeLink("./js/");
		return array(
			$root . "main.pack.js",
		);
	}
}
