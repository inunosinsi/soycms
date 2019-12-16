<?php

class ItemBlockCategoryUtil {

	public static function getConfig(){
		return SOYShop_DataSets::get("item_block_category.config", array(
			"count" => 0
		));
	}

	public static function saveConfig($values){
		SOYShop_DataSets::put("item_block_category.config", $values);
	}
}
