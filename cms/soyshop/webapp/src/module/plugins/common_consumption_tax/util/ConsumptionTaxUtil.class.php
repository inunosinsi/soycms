<?php

class ConsumptionTaxUtil{
	
	const METHOD_FLOOR = 0;	//端数を切り捨て
	const METHOD_ROUND = 1;	//端数を四捨五入
	const METHOD_CEIL = 2;	//端数を切り上げ
	
	function __construct(){
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
	}
	
	public static function getConfig(){
		return SOYShop_DataSets::get("consumption_tax.config", array(
			"method" => 0
		));
	}
	
	public static function saveConfig($values){
		SOYShop_DataSets::put("consumption_tax.config", $values);
	}
}
?>