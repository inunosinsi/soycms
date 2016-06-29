<?php

class EccubeDataImportUtil{
	
	public static function getConfig(){
		return SOYShop_DataSets::get("eccube_data_import_db_config", array(
			"host" => "",
			"port" => "",
			"db" => "",
			"user" => ""
		));
	}
	
	public static function saveConfig($values){
		SOYShop_DataSets::put("eccube_data_import_db_config", $values);
	}
	
	public static function clearTable(){
		SOYShop_DataSets::put("eccube_import.cat_cor_tbl", array());
		SOYShop_DataSets::put("eccube_import.cat_par_tbl", array());
		SOYShop_DataSets::put("eccube_data_import_db_config", array(
			"host" => "",
			"port" => "",
			"db" => "",
			"user" => ""
		));
	}
	
	public static function getMailConfig(){
		return SOYShop_DataSets::get("eccube_data_import_mail_config", array(
			"title" => "",
			"content" => ""
		));
	}
	
	public static function saveMailConfig($values){
		SOYShop_DataSets::put("eccube_data_import_mail_config", $values);
	}
}
?>