<?php
function multi_language_convert_image_filepath(array $arg, string $lang){
	$html = &$arg["html"];

	if(!function_exists("x_get_properties_by_img_tag")) SOY2::import("site_include.plugin.x_cls.func.fn", ".php");

	$lines = explode("\n", $html);
	$isChange = false;
	foreach($lines as $n => $line){
		$line = rtrim($line);
		if(!strlen($line) || is_bool(stripos($line, "<img"))) continue;	// イメージタグがない行はスルー

		preg_match_all('/<img.*?>/', $line, $tmp);
		if(!isset($tmp[0]) || !is_array($tmp[0]) || !count($tmp[0])) continue;
		
		foreach($tmp[0] as $imgTag){
			$props = x_get_properties_by_img_tag($imgTag);
			if(count($props) && isset($props["src"])){
				$filepath = x_build_filepath($props["src"]);
				$pos = strripos($filepath, ".");
				if(is_bool($pos)) continue;
				
				$extension = substr($filepath, $pos);
				$filepath = str_replace($extension, "_".$lang.$extension, $filepath);
				if(!file_exists($filepath)) continue;

				$src = str_replace($extension, "_".$lang.$extension, $props["src"]);
				$lines[$n] = str_replace($props["src"], $src, $line);
				$isChange = true;
			}
		}
	}
	
	return ($isChange) ? implode("\n", $lines) : null;
}