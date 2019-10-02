<?php

class SOYShopPluginUtil {

    public static function checkIsActive($pluginId){
		try{
			return (SOY2DAOFactory::create("plugin.SOYShop_PluginConfigDAO")->getByPluginId($pluginId)->getIsActive() == SOYShop_PluginConfig::PLUGIN_ACTIVE);
		}catch(Exception $e){
			return false;
		}
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
