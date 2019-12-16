<?php

class NewItemUtil{
	
	public static function getConfig(){
		return SOYShop_DataSets::get("new_item.config", array(
			"defaultSort" => "name",
			"isReverse" => 0,
            "tryCount" => 3
		));
	}
	
	public static function saveConfig($values){
		SOYShop_DataSets::put("new_item.config", $values);
	}
}
?>