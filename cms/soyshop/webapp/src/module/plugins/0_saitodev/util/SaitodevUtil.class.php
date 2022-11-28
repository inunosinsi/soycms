<?php

class SaitodevUtil {

	public static function getConfig(){
		return SOYShop_DataSets::get("0_saitodev.config", array());
	}

	public static function saveConfig($values){
		SOYShop_DataSets::put("0_saitodev.config", $values);
	}
}
