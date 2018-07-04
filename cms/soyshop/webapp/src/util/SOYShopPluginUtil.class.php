<?php

class SOYShopPluginUtil {

    public static function checkIsActive($pluginId){
    	$pluginDAO = SOY2DAOFactory::create("plugin.SOYShop_PluginConfigDAO");
		try{
			$plugin = $pluginDAO->getByPluginId($pluginId);
		}catch(Exception $e){
			$plugin = new SOYShop_PluginConfig();
		}

		return ($plugin->getIsActive() == SOYShop_PluginConfig::PLUGIN_ACTIVE);
    }

    public static function checkPluginListFile(){
		return (file_exists(SOY2::RootDir() . "logic/init/plugin/plugin.ini"));
	}

	public static function getPluginById($pluginId){
		static $dao, $plugins;
		if(isset($plugins[$pluginId])) return $plugins[$pluginId];

		if(is_null($dao)) $dao = SOY2DAOFactory::create("plugin.SOYShop_PluginConfigDAO");

		try{
			$plugins[$pluginId] = $dao->getByPluginId($pluginId);
		}catch(Exception $e){
			$plugins[$pluginId] = new SOYShop_PluginConfig();
		}

		return $plugins[$pluginId];
	}
}
