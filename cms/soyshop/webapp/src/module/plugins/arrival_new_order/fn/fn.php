<?php
function soyshop_get_arrival_config(string $pluginId){
	static $cfgs;
	if(!is_array($cfgs)) $cfgs = SOYShop_DataSets::get("arrival_configs", array());
	return (isset($cfgs[$pluginId])) ? $cfgs[$pluginId] : array("count" => 15, "period" => 12);
}

function soyshop_save_arrival_config(string $pluginId, array $posts){
	$cfgs = SOYShop_DataSets::get("arrival_configs", array());
	$cfgs[$pluginId] = $posts;
	SOYShop_DataSets::put("arrival_configs", $cfgs);
}
