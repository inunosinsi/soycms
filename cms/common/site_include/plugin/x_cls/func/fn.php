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

	// @ToDo javascript用の属性を見かけたら対応

	return $list;
}

/**
 * @param string
 * @return string
 */
function x_build_filepath(string $src){
	// httpから始まる絶対パスの場合 → スラッシュから始まる絶対パスに変換
	$res = strpos($src, "http");
	if(is_numeric($res) && $res === 0){
		$src = substr($src, strpos($src, "://")+3);
		$src = substr($src, strpos($src, "/"));
	}

	// 相対パスで最初が..の場合 → スラッシュから始まる絶対パスに変換
	$res = strpos($src, "..");
	if(is_numeric($res) && $res === 0){
		$dirs = explode("/", trim($_SERVER["REQUEST_URI"], "/"));
		$dirCnt = count($dirs);
		
		$i = 1;
		for(;;){
			$res = strpos($src, "../");
			if(is_bool($res)) break;

			//１つ上の階層
			$i++;
			if(!isset($dirs[$dirCnt-$i])) break;

			$srcDirs = explode("/", $src);
			for($j = count($srcDirs)-1; $j >=0; $j--){
				if($srcDirs[$j] == ".."){
					$srcDirs[$j] = $dirs[$dirCnt-$i];
				}
			}
			$src = implode("/", $srcDirs);
		}
		$src = "/".$src;
	}
	
	// スラッシュから始まる絶対パスの場合
	$res = strpos($src, "/");
	if(is_numeric($res) && $res === 0) return $_SERVER["DOCUMENT_ROOT"] . $src;

	$src = soy2_realpath($src);
	return (is_string($src)) ? $src : "";
}

/**
 * @param string
 * @return array
 */
function x_get_image_info_by_filepath(string $path){
	$path = x_build_filepath($path);
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
		if(strlen($v)){
			$newTag .= " " . $idx . "=\"" . $v . "\"";
		}else{
			$newTag .= " " . $idx;
		}	
	}
	$newTag .= ">";
	return $newTag;
}

/**
 * ファイルパスから拡張子を取得
 * @param string
 * @return string
 */
function x_get_extension_by_filepath(string $src){
	if(is_bool(strpos($src, "."))) return "";
	$ext = mb_strtolower(substr($src, strrpos($src, ".") + 1));
	switch($ext){
		case "jpg":
		case "jpeg":
			return "jpg";
		case "png":
			return "png";
		case "gif":
			return "gif";
		case "webp":
			return "webp";
		default:
			return "";
	}
}

/**
 * @param string, string, string
 * @return string
 */
function x_convert_file_extension(string $src, string $new="webp"){
	return substr($src, 0, strrpos($src, ".")).".".$new;
}