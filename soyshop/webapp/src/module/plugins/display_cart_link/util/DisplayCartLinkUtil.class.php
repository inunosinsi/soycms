<?php

class DisplayCartLinkUtil{
	
	function DisplayCartLinkUtil(){
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
	}
	
	public static function getConfig(){
		return SOYShop_DataSets::get("display_cart_link.config", array(
			"limitation" => 1
		));
	}
	
	public static function saveConfig($values){
		SOYShop_DataSets::put("display_cart_link.config", $values);
	}
}
?>