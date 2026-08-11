<?php

class ItemBlockUtil {

	public static function getConfig(){
		return SOYShop_DataSets::get("item_block.config", array(
			"count" => 0
		));
	}

	public static function saveConfig($values){
		SOYShop_DataSets::put("item_block.config", $values);
	}
}
