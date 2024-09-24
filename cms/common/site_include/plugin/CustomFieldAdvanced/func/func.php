<?php
/**
 * @param array, string, int(Entry:0, Label:1, Page:2)
 * @return bool
 */
function soycms_customfield_check_is_field(array $fields, string $typ, int $mode=0){
    static $arr;
    if(!is_array($arr)) $arr = array();
    if(!isset($arr[$mode])) $arr[$mode] = array();
	if(isset($arr[$mode][$typ]) && is_bool($arr[$mode][$typ])) return $arr[$mode][$typ];
	
	$arr[$mode][$typ] = false;
	if(!is_array($fields) || !count($fields)) return $arr[$mode][$typ];

	foreach($fields as $field){
		if($field->getType() == $typ){
			$arr[$mode][$typ] = true;
			return $arr[$mode][$typ] = true;;
		} 
	}

	return $arr[$mode][$typ];
}

/**
 * @param array
 * @return array
 */
function soycms_get_field_id_list(array $fields){
	if(!count($fields)) return array();
	$fieldIds = array();
	foreach($fields as $fieldId => $_dust){
		$fieldIds[] = $fieldId;
	}
	return $fieldIds;
}

/**
 * フィールドIDにcms:idの文字列を入れてしまった時の対処
 * @param string
 * @return string
 */
function soycms_customfield_fn_convert_cms_id_string(string $fieldId){
	preg_match('/cms:id=\"(.*)?\"/', $fieldId, $tmp);
	if(!isset($tmp[1])) return $fieldId;
	
	$fieldId = "cmsideq".trim($tmp[1]);

	return $fieldId;
}

/**
 * フィールドIDにcms:idの文字列を入れてしまった時の対処
 * @param string
 * @return string
 */
function soycms_customfield_fn_return_cms_id_string(string $fieldId){
	preg_match('/^cmsideq(.*)?/', $fieldId, $tmp);
	if(!isset($tmp[1])) return $fieldId;
	
	$fieldId = "cms:id=\"".trim($tmp[1])."\"";

	return $fieldId;
}