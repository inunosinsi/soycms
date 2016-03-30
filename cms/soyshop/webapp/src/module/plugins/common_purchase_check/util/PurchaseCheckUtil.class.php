<?php

class PurchaseCheckUtil{
	
	function PurchaseCheckUtil(){
		
	}
	
	public static function getConfig(){
		return SOYShop_DataSets::get("common_purchase_check.config", array(
			"paid" => 1
		));
	}
	
	public static function saveConfig($values){
		SOYShop_DataSets::put("common_purchase_check.config", $values);
	}
}
?>