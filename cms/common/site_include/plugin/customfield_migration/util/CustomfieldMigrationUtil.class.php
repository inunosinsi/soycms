<?php

class CustomfieldMigrationUtil {

	public static function getConfig(){
		SOY2::import("domain.cms.DataSets");
		return DataSets::get("customfield_migration.config", array(
			"CustomField" => array(),
			"CustomFieldAdvanced" => array()
		));
	}

	//cfCntはカスタムフィールドの方の設定、cfaCnfはカスタムフィールドアドバンスドの方の設定
	public static function saveConfig($cfCnf, $cfaCnf){
		SOY2::import("domain.cms.DataSets");
		DataSets::put("customfield_migration.config", array(
			"CustomField" => $cfCnf,
			"CustomFieldAdvanced" => $cfaCnf
		));
	}

	public static function getAllEntryIds(){
		try{
			$res = self::_dao()->executeQuery("SELECT id FROM Entry");
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return array();

		$ids = array();
		foreach($res as $v){
			if(!isset($v["id"]) || !is_numeric($v["id"])) continue;
			$ids[] = (int)$v["id"];
		}
		return $ids;
	}

	public static function migrateConfig($conf){
		$cnf = array();	//値があるものだけ整理する
		foreach($conf as $key => $v){
			if(!isset($v) || !strlen($v)) continue;
			$cnf[$key] = $v;
		}
		return $cnf;
	}

	public static function getCustomfieldValuesByEntryId($entryId){
		try{
			$res = self::_dao()->executeQuery("select custom_field from Entry where id = :id", array(":id" => $entryId));
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res) || is_null($res[0]["custom_field"])) return array();

		$customfields = soy2_unserialize($res[0]["custom_field"]);
		if(!count($customfields)) return array();

		$values = array();
		foreach($customfields as $cf){
			$values[$cf->getId()] = $cf;
		}
		return $values;
	}

	public static function getCustomfieldAdvancedValuesByEntryId($entryId){
		try{
			return self::_dao()->getByEntryId($entryId);
		}catch(Exception $e){
			return array();
		}
	}

	private static function _dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("cms.EntryAttributeDAO");
		return $dao;
	}
}
