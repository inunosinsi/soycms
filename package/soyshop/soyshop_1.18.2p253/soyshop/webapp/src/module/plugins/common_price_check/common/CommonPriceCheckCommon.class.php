<?php

class CommonPriceCheckCommon{
	
	public static function getConfig(){
		return SOYShop_DataSets::get("common_price_check", array(
			"price" => 10000,
			"error" => "合計金額が足りません"
		));
	}
}
?>