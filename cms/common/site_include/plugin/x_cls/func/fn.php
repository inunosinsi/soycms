<?php

/**
 * @param string
 * @return array
 */
function x_get_properties_by_img_tag(string $imgTag){
	$list = array();

	// prop="***"の方を調べる
	preg_match_all('/[a-zA-Z_0-9\-]*?=\".*?\"/', $imgTag, $tmp);
	if(isset($tmp[0]) && is_array($tmp[0]) && count($tmp[0])){
		foreach($tmp[0] as $p){
			$prop = explode("=", $p);
			if(!isset($prop[1])) continue;
			$v = trim(trim($prop[1], "\""));
			if(!strlen($v)) continue;
			$idx = trim($prop[0]);
			$list[$idx] = $v;
		}
	}

	// prop='***'の方を調べる
	preg_match_all("/[a-zA-Z_0-9\-]*?='.*?'/", $imgTag, $tmp);
	if(isset($tmp[0]) && is_array($tmp[0]) && count($tmp[0])){
		foreach($tmp[0] as $p){
			$prop = explode("=", $p);
			if(!isset($prop[1])) continue;
			$v = trim(trim($prop[1], "'"));
			if(!strlen($v)) continue;
			$idx = trim($prop[0]);
			$list[$idx] = $v;
		}
	}

	return $list;
}

/**
 * @param string
 * @return array
 */
function x_get_image_info_by_filepath(string $path){
	if(strpos($path, "/") !== 0){
		// @ToDo スラッシュから始まらない場合はドメインを削除
	}

	$path = $_SERVER["DOCUMENT_ROOT"] . $path;
	if(!file_exists($path)) return array();

	$info = getimagesize($path);
	return array("width" => $info[0], "height" => $info[1]);
}

/**
 * @param array, array
 * @return array
 */
function x_merge_properties(array $props, array $info){
	foreach($info as $idx => $v){
		$props[$idx] = $v;
	}
	return $props;
}

/**
 * @param string, array
 * @return string
 */
function x_rebuild_image_tag(string $oldTag, array $props){
	if(!is_array($props) || !count($props)) return $oldTag;
	$newTag = "<img";
	foreach($props as $idx => $v){
		$newTag .= " " . $idx . "=\"" . $v . "\"";
	}
	$newTag .= ">";
	return $newTag;
}