<?php

class ConfigFooterMenuPage extends HTMLPage{

	function __construct(){
		parent::__construct();

		$list = self::_getPluginList();
		DisplayPlugin::toggle("plugin", count($list));

		$this->createAdd("plugin_list", "_common.Config.PluginListComponent", array(
			"list" => $list,
			"configPageLink" => SOY2PageController::createLink("Config.Detail")
		));
	}

	private function _getPluginList(){
    	SOYShopPlugin::load("soyshop.config");
		return SOYShopPlugin::invoke("soyshop.config", array(
			"mode" => "list"
		))->getList();
    }
}
