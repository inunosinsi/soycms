<?php

class PaymentDaibikiUtil {

	public static function getConfig(){
		return SOYShop_DataSets::get("payment_daibiki.config", array(
			"auto_calc" => 1,
			"include_delivery_price" => 0
		));
	}

	public static function saveConfig($values){
		return SOYShop_DataSets::put("payment_daibiki.config", $values);
	}

	public static function getPricesConfig($appendBlank = false){
		$config = SOYShop_DataSets::get("payment_daibiki.price", array(
			0 => 300
		));

		if($appendBlank){
			$config[""] = "";
		}

		return $config;
	}

	public static function savePricesConfig($values){
		SOYShop_DataSets::put("payment_daibiki.price",$values);
	}

	public static function getForbiddenConfig($appendBlank = false){
		$items = SOYShop_DataSets::get("payment_daibiki.forbidden", array());
		if($appendBlank){
			$items[] = "";
		}
		return $items;
	}

	public static function saveForbiddenConfig($values){
		SOYShop_DataSets::put("payment_daibiki.forbidden",$values);
	}

	public static function getPricesByRegionConfig(){
		$configs = SOYShop_DataSets::get("payment_daibiki.region_price", array());
		if(count($configs)){
			ksort($configs);
		}

		foreach($configs as $area => $config){
			if(!count($configs[$area])){
				$configs[$area][0] = 300;
			}
		}

		return $configs;
	}

	public static function savePricesByRegionConfig($values){
		SOYShop_DataSets::put("payment_daibiki.region_price", $values);
	}

	public static function getDescriptionConfig(){
		return SOYShop_DataSets::get("payment_daibiki.description","代引きでのお支払いです。手数料は#PRICE#円です。");
	}

	public static function saveDescriptionConfig($value){
		SOYShop_DataSets::put("payment_daibiki.description",$value);
	}

	public static function getMailConfig(){
		return SOYShop_DataSets::get("payment_daibiki.mail","支払方法：代金引換");
	}

	public static function saveMailConfig($value){
		SOYShop_DataSets::put("payment_daibiki.mail",$value);
	}
}
?>
