<?php
class CustomIconFieldInstall extends SOYShopPluginInstallerBase{

	function onInstall(){
		SOY2::import("module.plugins.custom_icon_field.util.CustomIconFieldUtil");
		$dir = CustomIconFieldUtil::getIconDirectory();
		if(!is_dir($dir)) mkdir($dir);
	}

	function onUnInstall(){

	}
}
SOYShopPlugin::extension("soyshop.plugin.install", "custom_icon_field", "CustomIconFieldInstall");
