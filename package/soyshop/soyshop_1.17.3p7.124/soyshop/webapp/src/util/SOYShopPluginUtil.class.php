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
}
?>