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
	// 管理画面側でも動作する為の処理
	if(!defined("_SITE_ROOT_") && class_exists("UserInfoUtil")){
		$siteRoot = UserInfoUtil::getSite()->getPath();
	}else{
		$siteRoot = _SITE_ROOT_;
	}

	$xamppRoot = str_replace("\\", "/", $siteRoot);
	if($siteRoot != $xamppRoot){	// xampp
		return ltrim(str_replace($_SERVER["DOCUMENT_ROOT"], "", $xamppRoot), "/");
	}else{
		$siteId = ltrim(str_replace($_SERVER["DOCUMENT_ROOT"], "", $siteRoot), "/");
		if(!strlen($siteId) || $siteId == "//"){
			$siteRoot = rtrim($siteRoot, "/");
			$siteId = trim(trim(substr($siteRoot, strrpos($siteRoot, "/"))), "/");
		}
		return $siteId;
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
		if(soy2_strpos($l, "include_once(") < 0) continue;
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

/**
 * $_SERVER["REQUEST_URI"]から記事のエイリアスを取得する
 * @return string
 */
function soycms_get_alias_by_request_uri(){
	if(!isset($_SERVER["REQUEST_URI"])) return "";
	
	$siteId = soycms_get_site_id_by_frontcontroller();
	$reqUri = $_SERVER["REQUEST_URI"]; 
	if(preg_match('/^\/'.$siteId.'\//', $reqUri) === 1){
		$reqUri = substr($reqUri, strlen($siteId)+1);
	}

	if(preg_match('/^\/'.$_SERVER["SOYCMS_PAGE_URI"].'\//', $reqUri) === 1){
		$reqUri = substr($reqUri, strlen($_SERVER["SOYCMS_PAGE_URI"])+1);
	}
	
	$reqUri = trim($reqUri, "/");
	foreach(array("/", "#", "?") as $char){
		if(soy2_strpos($reqUri, $char) < 0) continue;
		$reqUri = substr($reqUri, 0, soy2_strpos($reqUri, $char));
	}
	
	return $reqUri;
}
