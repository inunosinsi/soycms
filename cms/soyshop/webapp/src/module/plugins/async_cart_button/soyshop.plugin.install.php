<?php
class AsyncCartButtonInstall extends SOYShopPluginInstallerBase{
	
	function onInstall(){
		
		$filePath = SOYSHOP_SITE_DIRECTORY . "themes/sample/soyshop_async_add_item.png";
		if(!file_exists($filePath)){
			$path = dirname(__FILE__) . "/img/soyshop_async_add_item.png";
			copy($path,$filePath);
		}
	}
	
	function onUnInstall(){
		
	}
}
SOYShopPlugin::extension("soyshop.plugin.install","async_cart_button","AsyncCartButtonInstall");
?>