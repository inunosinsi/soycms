<?php

class BreadcrumbUtil{
	
	function BreadcrumbUtil(){
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
	}
	
	public static function getConfig(){
		return SOYShop_DataSets::get("common_breadcrumb.config", array(
			"displayChild" => 1
		));
	}
	
	public static function saveConfig($values){
		SOYShop_DataSets::put("common_breadcrumb.config", $values);
	}
}
?>