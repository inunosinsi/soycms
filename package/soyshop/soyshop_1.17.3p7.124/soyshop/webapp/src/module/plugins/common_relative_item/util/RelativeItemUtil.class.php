<?php

class RelativeItemUtil{
	
	public static function getConfig(){
		return SOYShop_DataSets::get("relative_item.config", array(
			"defaultSort" => "name",
			"isReverse" => 0
		));
	}
	
	public static function saveConfig($values){
		SOYShop_DataSets::put("relative_item.config", $values);
	}
}
?>