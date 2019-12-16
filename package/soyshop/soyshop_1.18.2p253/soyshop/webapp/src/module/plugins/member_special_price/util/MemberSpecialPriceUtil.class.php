<?php

class MemberSpecialPriceUtil{

	public static function getConfig(){
		return SOYShop_DataSets::get("member_special_price.config", array());
	}

	public static function saveConfig($values){
		return SOYShop_DataSets::put("member_special_price.config", $values);
	}

}
