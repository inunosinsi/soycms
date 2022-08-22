<?php
if(!function_exists("soycms_customfield_check_is_field")) include_once(SOY2::RootDir() . "site_include/plugin/CustomFieldAdvanced/func/func.php");

class LabelCustomfieldUtil {

	public static function checkIsEntryField(array $fields){
		return soycms_customfield_check_is_field($fields, "entry", 1);
	}

	public static function checkIsLabelField(array $fields){
		return soycms_customfield_check_is_field($fields, "label", 1);
	}

	public static function checkIsListField(array $fields){
		return soycms_customfield_check_is_field($fields, "list", 1);
	}

	public static function checkIsDlListField(array $fields){
		return soycms_customfield_check_is_field($fields, "dllist", 1);
	}
}
