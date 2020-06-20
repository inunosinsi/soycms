<?php
/**
 * @class Config.DetailPage
 * @date 2009-07-28T16:04:26+09:00
 * @author SOY2HTMLFactory
 */
class DetailPage extends WebPage{

	function __construct(){

		$pluginId = (isset($_GET["plugin"])) ? $_GET["plugin"] null;
		$module = soyshop_get_plugin_object($pluginId);
		if(is_null($module->getId())) SOY2PageController::jump("Site.Config");

		parent::__construct();

		SOYShopPlugin::load("soyshop.config.site", $module);
		$delegate = SOYShopPlugin::invoke("soyshop.config.site", array(
			"mode" => "config"
		));

		$this->addLink("plugin_detail_link", array(
			"link" => SOY2PageController::createLink("Plugin.Detail." . $module->getId())
		));

		$this->addLabel("plugin_title", array(
			"text" => $delegate->getTitle()
		));

		$this->addLabel("plugin_config", array(
			"html" => $delegate->getConfigPage()
		));
	}
}
