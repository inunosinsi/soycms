<?php

//新着プラグイン5種をインストール
$plIds = array("new_order", "item_stock", "update_item", "update_page", "shop_info");

$logic = SOY2Logic::createInstance("logic.plugin.SOYShopPluginLogic");
$logic->prepare();
$logic->searchModules();

$pluginDao = SOY2DAOFactory::create("plugin.SOYShop_PluginConfigDAO");

for($i = 0; $i < count($plIds); $i++){
	$pluginId = "arrival_" . $plIds[$i];
	$logic->installModule($pluginId);

	$plugin = soyshop_get_plugin_object($pluginId);
	if(is_null($plugin->getId())) continue;

	//順番の登録
	$displayOrder = ($i === 0) ? 1 : 10 + $i;
	$plugin->setDisplayOrder($displayOrder);

	try{
		$pluginDao->update($plugin);
	}catch(Exception $e){
		var_dump($e);
	}
}
