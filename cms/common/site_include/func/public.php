<?php
/**
 * 記事一覧から記事IDの一覧を取得
 * @param array
 * @return array
 */
function soycms_get_entry_id_by_entries(array $entries){
	if(!count($entries)) return array();

	$ids = array();
	foreach($entries as $entry){
		$ids[] = (int)$entry->getId();
	}
	return $ids;
}

/**
 * @return string
 */
function soycms_get_site_id_by_frontcontroller(){
	$xamppRoot = str_replace("\\", "/", _SITE_ROOT_);
	if(_SITE_ROOT_ != $xamppRoot){	// xampp
		return ltrim(str_replace($_SERVER["DOCUMENT_ROOT"], "", $xamppRoot), "/");
	}else{
		return ltrim(str_replace($_SERVER["DOCUMENT_ROOT"], "", _SITE_ROOT_), "/");
	}
}

/**
 * @return bool
 */
function soycms_check_is_root_site_by_frontcontroller(){
	if(!file_exists($_SERVER["DOCUMENT_ROOT"] . "/index.php")) return false;
	$lines = explode("\n", file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/index.php"));
	if(!count($lines)) return false;

	foreach($lines as $l){
		if(is_bool(strpos($l, "include_once("))) continue;
		preg_match('/include_once\(\"(.*?)\/index.php\"\)/', $l, $tmp);
		if($tmp[1] == soycms_get_site_id_by_frontcontroller()) return true;
	}

	return false;
}

/**
 * @param bool
 * @return string
 */
function soycms_get_site_url_by_frontcontroller(bool $isAbsolute=false){
	if($isAbsolute){
		$u = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") ? "https" : "http";
		$u .= "://" . $_SERVER["HTTP_HOST"] . "/";
	}else{
		$u = "/";
	}
	$u .= soycms_get_site_id_by_frontcontroller() . "/";
	return $u;
}

/**
 * @param bool
 * @return string
 */
function soycms_get_site_publish_url_by_frontcontroller(bool $isAbsolute=false){
	$u = soycms_get_site_url_by_frontcontroller($isAbsolute);
	if(!soycms_check_is_root_site_by_frontcontroller()) return $u;
	return dirname($u) . "/";	
}

/**
 * @param bool
 * @return string
 */
function soycms_get_page_url_by_frontcontroller(bool $isAbsolute=false){
	$u = soycms_get_site_publish_url_by_frontcontroller($isAbsolute);
	if(isset($_SERVER["SOYCMS_PAGE_URI"]) && strlen($_SERVER["SOYCMS_PAGE_URI"])) $u .= $_SERVER["SOYCMS_PAGE_URI"] . "/";
	return $u;
}

function soycms_jump_notfound_page(){
	header("Location:".soycms_get_site_publish_url_by_frontcontroller()."_notfound");
	exit;
}