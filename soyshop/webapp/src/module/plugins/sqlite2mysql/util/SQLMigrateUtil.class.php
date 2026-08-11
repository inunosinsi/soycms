<?php

class SQLMigrateUtil {

	public static function getConfig(){
		return SOYShop_DataSets::get("sqlite_2_mysql.config", array(
			"host" => "",
			"port" => "",
			"dbname" => "",
			"user" => "",
			"pass" => ""
		));
	}

	public static function saveConfig($values){
		SOYShop_DataSets::put("sqlite_2_mysql.config", $values);
	}
}
