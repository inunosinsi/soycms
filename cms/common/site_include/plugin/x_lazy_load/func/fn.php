<?php
if(!function_exists("x_get_properties_by_img_tag")) SOY2::import("site_include.plugin.x_cls.func.fn", ".php");

/**
 * @param string
 * @return array
 */
function x_get_tags(string $line){
	$tags = explode("<", $line);
	$tmps = array();
	foreach($tags as $idx => $tag){
		$tag = "<".trim($tag);
		if(is_bool(strpos($tag, "<")) || is_bool(strpos($tag, ">")) || substr_count($tag, "<") !== 1 || substr_count($tag, ">") !== 1) continue;
		$tmps[] = $tag;
	}
	return $tmps;
}

/**
 * @param array
 * @return string
 */
function x_get_tag_by_element(array $tags, string $ele="img"){
	if(!count($tags)) return "";

	$idx = 0;
	foreach($tags as $i => $tag){
		preg_match('/<.*'.$ele.'.*>/i', $tag, $tmp);
		if(!isset($tmp[0])) continue;

		$idx = $i;
		break;
	}
	if($idx === 0) return "";
	return (isset($tags[$idx])) ? $tags[$idx] : "";
}

/**
 * @param array
 * @return string
 */
function x_get_embedded_element_tag(array $tags){
	if(!count($tags)) return "";
	$tag = x_get_tag_by_element($tags, "img");
	if(strlen($tag)) return $tag;
	return x_get_tag_by_element($tags, "iframe");
}

/**
 * @param array
 * @return string
 */
function x_get_parent_element_tag_by_embedded_tag(array $tags){
	if(!count($tags)) return "";
	$tag = x_get_tag_by_element($tags, "img");
	if(!strlen($tag)) $tag = x_get_tag_by_element($tags, "iframe");
	if(!strlen($tag)) return "";

	$ele = x_get_tag_element($tag);

	$idx = 0;
	foreach($tags as $i => $tag){
		preg_match('/<.*'.$ele.'.*>/i', $tag, $tmp);
		if(!isset($tmp[0])) continue;

		$idx = $i;
		break;
	}
	if($idx === 0) return "";
	return (isset($tags[$idx-1])) ? $tags[$idx-1] : "";
}

/**
 * @param string, string
 * @return string
 */
function get_loading_property(string $embedType="img", string $pluginId="x_lazy_load"){
	static $cnt;
	if(!is_array($cnt)) $cnt = array();
	if(!isset($cnt[$pluginId])) $cnt[$pluginId] = 0;
	
	switch($embedType){
		case "img":
			switch($cnt[$pluginId]++){
				case 0:	//最初の画像は必ずloading="eager"
					$p = "eager";
					break;
				case 1:	//2番目の画像は必ずloading="auto"
					$p = "auto";
					break;
				default://残りはすべてloading="lazy"
					$p = "lazy";
					break;
			}
			break;
		case "iframe":
		default:
			$p = "lazy";
			break;
	}

	return $p;
}