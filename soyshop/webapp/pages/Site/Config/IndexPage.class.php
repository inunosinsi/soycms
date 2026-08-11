<?php
/**
 * @class Site.Config.IndexPage
 * @date 2009-11-26T18:13:01+09:00
 * @author SOY2HTMLFactory
 */
class IndexPage extends WebPage{

	function __construct(){
		SOY2PageController::jump("");
		//parent::__construct();

		// $this->createAdd("plugin_list", "_common.Site.Config.PluginListComponent", array(
		// 	"list" => $this->getPluginList(),
		// 	"configPageLink" => SOY2PageController::createLink("Site.Config.Detail")
		// ));
	}

	function getPluginList(){
    	$array = array();

    	SOYShopPlugin::load("soyshop.config.site");
		return SOYShopPlugin::invoke("soyshop.config.site", array(
			"mode" => "list"
		))->getList();
    }
}
