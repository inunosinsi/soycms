<?php
class CustomIconFieldInstall extends SOYShopPluginInstallerBase{
	
	function onInstall(){
		
		$siteDir = SOYSHOP_SITE_DIRECTORY;
		$iconDir = $siteDir . "files/custom-icons/";
		
		if(!is_dir($iconDir)){
			mkdir($iconDir);
		}
		
	}
	
	function onUnInstall(){
		
	}
}
SOYShopPlugin::extension("soyshop.plugin.install", "custom_icon_field", "CustomIconFieldInstall");
?>
