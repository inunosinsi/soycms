<?php

class RecommendItemUtil{
	
	public static function getConfig(){
		return SOYShop_DataSets::get("recommend_item.config", array(
			"defaultSort" => "name",
			"isReverse" => 0
		));
	}
	
	public static function saveConfig($values){
		SOYShop_DataSets::put("recommend_item.config", $values);
	}
}
?>