<?php
if(file_exists(dirname(SOYSHOP_ROOT)."/common/db/inquiry.db")){
	$pluginId = "0_inquiry_failure_to_check";
	SOY2Logic::createInstance("logic.plugin.SOYShopPluginLogic")->installModule($pluginId);

	// 順番の変更
	$pluginDao = SOY2DAOFactory::create("plugin.SOYShop_PluginConfigDAO");
	
	// 順番の登録
	try{
		$plugin = $pluginDao->getByPluginId($pluginId);
		$plugin->setDisplayOrder(1);
		$pluginDao->update($plugin);
	}catch(Exception $e){
		//
	}
}
