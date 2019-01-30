<?php
$pluginId = "0_saitodev";
$logic = SOY2Logic::createInstance("logic.plugin.SOYShopPluginLogic");
$logic->prepare();
$logic->searchModules();

try{
	$logic->installModule($pluginId);
}catch(Exception $e){
	//
}

$pluginDao = SOY2DAOFactory::create("plugin.SOYShop_PluginConfigDAO");

//順番の登録
try{
	$plugin = $pluginDao->getByPluginId($pluginId);
}catch(Exception $e){
	var_dump($e);
}

$plugin->setDisplayOrder(1);

try{
	$pluginDao->update($plugin);
}catch(Exception $e){
	var_dump($e);
}
