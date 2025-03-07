<?php

function cfb_fn_get_customfield_list(){
	if(!class_exists("CustomFieldAdvanced")) SOY2::import("site_include.plugin.CustomFieldAdvanced.CustomFieldAdvanced", "php");
		$advObj = CMSPlugin::loadPluginConfig("CustomFieldAdvanced");
		if(!$advObj instanceof CustomFieldPluginAdvanced || !is_array($advObj->customFields) || !count($advObj->customFields)) return array();

		$_arr = array();
		$notSupported = array("entry", "pair", "list", "dllist");	// 未対応のフィールド
		foreach($advObj->customFields as $fieldId => $field){
			if(is_numeric(array_search((string)$field->getType(), $notSupported))) continue;
			$_arr[$fieldId] = $field->getLabel();
		}

		return $_arr;
}

function cfb_fn_get_label_list(){
	$dao = new SOY2DAO();
	
	try{
		$res = $dao->executeQuery(
			"SELECT id, caption FROM Label ".
			"WHERE id IN (".
				"SELECT label_id FROM EntryLabel ".
				"GROUP BY label_id ".
				"HAVING COUNT(*) > 0".
			")"
		);
	}catch(Exception $e){
		$res = array();
	}

	if(!count($res)) return array();

	$_arr = array();

	foreach($res as $v){
		$_arr[$v["id"]] = $v["caption"];
	}

	return $_arr;
}
