<?php
/**
 * @class Config.DetailPage
 * @date 2009-07-28T16:04:26+09:00
 * @author SOY2HTMLFactory
 */
class DetailPage extends WebPage{

	public $module;

	function __construct(){

		$plugin = @$_GET["plugin"];

		if(!$plugin){
			SOY2PageController::jump("Site.Config");
		}

		$dao = SOY2DAOFactory::create("plugin.SOYShop_PluginConfigDAO");
    	$logic = SOY2Logic::createInstance("logic.plugin.SOYShopPluginLogic");

		try{
    		$this->module = $dao->getByPluginId($plugin);
		}catch(Exception $e){
			SOY2PageController::jump("Site.Config");
		}

		parent::__construct();

		SOYShopPlugin::load("soyshop.config.site", $this->module);
		$delegate = SOYShopPlugin::invoke("soyshop.config.site", array(
			"mode" => "config"
		));

		$this->addLink("plugin_detail_link", array(
			"link" => SOY2PageController::createLink("Plugin.Detail." . $this->module->getId())
		));

		$this->addLabel("plugin_title", array(
			"text" => $delegate->getTitle()
		));

		$this->addLabel("plugin_config", array(
			"html" => $delegate->getConfigPage()
		));
	}
}
?>