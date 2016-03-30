<?php

class AutoRankingUtil{
	
	function AutoRankingUtil(){
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
	}
	
	public static function getConfig(){
		return SOYShop_DataSets::get("auto_ranking.config", array(
			"count" => 3,
			"period" => 30
		));
	}
	
	public static function setConfig($values){
		$values["count"] = soyshop_convert_number($values["count"], 0);
		$values["period"] = soyshop_convert_number($values["period"], 0);
		SOYShop_DataSets::put("auto_ranking.config", $values);
	}
}
?>