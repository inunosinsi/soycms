<?php

class WordPressImportEntryUtil {

	public static function getConfig(){
		return self::_config();
	}

	private static function _config(){
		SOY2::import("domain.cms.DataSets");
		return DataSets::get("wp_imp_entry.config", array(
			"name" => "",
			"user" => "",
			"password" => "",
			"host" => "localhost"
		));
	}

	public static function saveConfig($values){
		SOY2::import("domain.cms.DataSets");
		DataSets::put("wp_imp_entry.config", $values);
	}

	public static function isDBConfig(){
		$cnf = self::_config();
		return (strlen($cnf["name"]) && strlen($cnf["user"]));
	}
}
