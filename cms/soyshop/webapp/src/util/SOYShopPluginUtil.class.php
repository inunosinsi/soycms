<?php

class SOYShopPluginUtil {

    public static function checkIsActive($pluginId){
		return (soyshop_get_plugin_object($pluginId)->getIsActive() == SOYShop_PluginConfig::PLUGIN_ACTIVE);
    }

    public static function checkPluginListFile(){
		return (file_exists(SOY2::RootDir() . "logic/init/plugin/plugin.ini"));
	}

	public static function getPluginById($pluginId){
		return soyshop_get_plugin_object($pluginId);
	}
}
