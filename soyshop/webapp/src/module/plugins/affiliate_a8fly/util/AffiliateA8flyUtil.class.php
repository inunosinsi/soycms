<?php

class AffiliateA8flyUtil {

	public static function getConfig(){
		return SOYShop_DataSets::get("affiliate_a8fly.config", array(
			"id" => ""
		));
	}

	public static function saveConfig($values){
		$values["sandbox"] = (isset($values["sandbox"])) ? 1 : null;
		SOYShop_DataSets::put("affiliate_a8fly.config", $values);
	}
}
