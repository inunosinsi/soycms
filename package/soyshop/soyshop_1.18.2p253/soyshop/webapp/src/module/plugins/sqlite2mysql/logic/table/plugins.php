<?php

function register_plugins($stmt){
	$dao = SOY2DAOFactory::create("plugin.SOYShop_PluginConfigDAO");

	$i = 0;
	$j = 1;
	for(;;){
		$dao->setOrder("id ASC");
		$dao->setLimit(RECORD_LIMIT);
		$dao->setOffset(RECORD_LIMIT * $i++);
		try{
			$plugins = $dao->get();
			if(!count($plugins)) break;
		}catch(Exception $e){
			break;
		}

		foreach($plugins as $plugin){
			if(!$plugin->getIsActive()) continue;
			$stmt->execute(array(
				":id" => $j++,
				":plugin_id" => $plugin->getPluginId(),
				":plugin_type" => $plugin->getType(),
				":config" => $plugin->getConfig(),
				":display_order" => $plugin->getDisplayOrder(),
				":is_active" => 1
			));
		}
	}
}
