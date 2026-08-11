<?php

class DiscountItemStockUtil{
	
	function DiscountItemStockUtil(){
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
	}
	
	public static function getConfig(){
		$configs = SOYShop_DataSets::get("discount_item_stock.config", null);
		if(is_null($configs) || count($configs) === 0) $configs = array(array("stock" => 1, "discount" => 0));
		return $configs;
	}
	
	public static function saveConfig($values){
		SOYShop_DataSets::put("discount_item_stock.config", $values);
	}
}
?>