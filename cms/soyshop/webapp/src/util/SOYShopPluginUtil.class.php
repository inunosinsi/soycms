<?php

class SOYShopPluginUtil {

    public static function checkIsActive($pluginId){
		if(!function_exists("soyshop_get_plugin_object")) SOY2::import("base.func.common", ".php");
		return (soyshop_get_plugin_object($pluginId)->getIsActive() == SOYShop_PluginConfig::PLUGIN_ACTIVE);
    }

    public static function checkPluginListFile(){
		return (file_exists(SOY2::RootDir() . "logic/init/plugin/plugin.ini"));
	}

	public static function getPluginById($pluginId){
		if(!function_exists("soyshop_get_plugin_object")) SOY2::import("base.func.common", ".php");
		return soyshop_get_plugin_object($pluginId);
	}
}
