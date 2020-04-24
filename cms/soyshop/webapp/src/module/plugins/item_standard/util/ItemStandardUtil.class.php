<?php
SOY2DAOFactory::importEntity("SOYShop_DataSets");

class ItemStandardUtil{

	public static function getConfig(){
		return SOYShop_DataSets::get("item_standard.config", array());
	}

	public static function saveConfig($values){
		SOYShop_DataSets::put("item_standard.config", $values);
	}
}
