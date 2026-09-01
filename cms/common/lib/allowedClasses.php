<?php
/**
 * $allowedに配列形式でunserializeを許可するクラス名を指定します。
 * $allowed = array();
 */
/**
 * シリアライズ化した値を復元する時に許可するクラス一覧をキャッシュ化する為のディレクトリ
 * @return string
 */
function soycms_get_unserialize_allowed_classes_cache_filepath(){
	if(defined("_SITE_ROOT_")){	// SOY CMSの場合
		$_dir = _SITE_ROOT_."/.cache/";
	}else if(defined("SOYSHOP_SITE_DIRECTORY")){
		$_dir = SOYSHOP_SITE_DIRECTORY.".cache/";
	}else{
		$siteId = (string)SOY2ActionSession::getUserSession()->getAttribute("siteId");
		if(!strlen($siteId)) return "";
		
		// サブドメイン対策でdepth=3
		$fs = soy2_scanfiles($_SERVER["DOCUMENT_ROOT"], 3);
		foreach($fs as $f){
			//フロントコントローラを探して、ファイルが格納されているディレクトリを探す
			if(soy2_strpos($f, "/".$siteId."/index.php") > 0){
				$_dir = dirname($f);
				break;
			}
		}
			
		$_dir .= "/.cache/";
	}

	return (file_exists($_dir)) ? $_dir."unserialize_allowed_classes" : "";
}

/**
 * @return bool
 */
function soycms_is_unserialize_allowed_classes_cache_file(){
	$path = soycms_get_unserialize_allowed_classes_cache_filepath();
	return (strlen($path) && file_exists($path));
}

if(soycms_is_unserialize_allowed_classes_cache_file()){
	$allowed = explode(",", file_get_contents(soycms_get_unserialize_allowed_classes_cache_filepath()));
}else{
	/**
	 * 各プラグインファイルからクラス名を抽出
	 * @param string
	 * @return string
	 */
	function soycms_extract_plugin_class_name(string $filepath){
		if (!is_file($filepath)) return "";

		$content = file_get_contents($filepath);
		if (preg_match('/([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)::register\w*\s*\(/', $content, $matches)) {
			return $matches[1];
		}

	    return "";
	}

	/**
	 * 各プラグインファイルからプラグインIDを抽出
	 * @param string
	 * @return string
	 */
	function soycms_extract_plugin_id(string $filepath){
		if (!is_file($filepath)) return "";
	    
		$content = file_get_contents($filepath);
		if (preg_match('/PLUGIN_ID\s*=\s*["\']([^"\']+)["\']/i', $content, $matches)) {
			return $matches[1];
	   	}
	    
	    return "";
	}

	/**
	 * シリアライズ化した値を復元する時に許可するクラス一覧
	 * @param array
	 */
	function soycms_create_unserialize_allowed_classes_cache(array $allowedClasses){
		$path = soycms_get_unserialize_allowed_classes_cache_filepath();
		if(strlen($path)) file_put_contents($path, implode(",", $allowedClasses));
	}

	$allowed = (function(){
		$_arr = array(
			/** 共通 **/
			"stdClass",

			/** SOY CMS **/
			"Site",
			"CustomField",	//これは自動で読み込めない

			/** SOY Shop **/
			"MyPageLogic",
			"CartLogic",
			"AdminCartLogic",
			"SOYShop_ComplexPageBlock"
		);

		if(!defined("SOY2_UNSERIALIZE_CMS_COMMON")){
			if(defined("CMS_COMMON")){
				$commonDir = CMS_COMMON;
			}else{
				if(soy2_strpos(SOY2::rootDir(), "soyshop/webapp/src")){
					$commonDir = str_replace("soyshop/webapp/src", "common", SOY2::rootDir());
				}else{
					$commonDir = SOY2::RootDir();
				}
			}
			define("SOY2_UNSERIALIZE_CMS_COMMON", $commonDir);
		}

		// プラグインのクラス名をリスト化
		$fs = soy2_scanfiles(SOY2_UNSERIALIZE_CMS_COMMON."site_include/plugin/");
		if(count($fs)){
			foreach($fs as $f){
				if(pathinfo($f, PATHINFO_FILENAME) != basename(dirname($f))) continue;
				$cName = soycms_extract_plugin_class_name($f);
				if(strlen($cName) > 0) $_arr[] = $cName;

				$pluginId = soycms_extract_plugin_id($f);
				if(strlen($pluginId) > 0 && $cName != $pluginId && is_bool(array_search($pluginId, $_arr))) $_arr[] = $pluginId;
			}
		}
		

		// ブロックのリスト化
		$ds = soy2_scandir(SOY2_UNSERIALIZE_CMS_COMMON."site_include/block/");
		foreach($ds as $d){
			if(soy2_strpos($d, "Component") < 0) continue;
			$_arr[] = $d;
		}

		$domainDirs = array();
		// SOYCMSのドメイン
		$domainDirs[] = SOY2_UNSERIALIZE_CMS_COMMON."domain/";

		// SOYShopのドメイン
		$domainDirs[] = dirname(SOY2_UNSERIALIZE_CMS_COMMON)."/soyshop/webapp/src/domain/";
		
		foreach($domainDirs as $domainDir){
			$fs = soy2_scanfiles($domainDir);
			foreach($fs as $f){
				if(soy2_strpos($f, "DAO") >= 0) continue;
				$cName = preg_replace('/\.(class\.)?php$/', '', basename($f));
				if(strlen($cName) > 0 && is_bool(array_search($cName, $_arr))) $_arr[] = $cName;
			}
		}
		
		return $_arr;
	})();

	soycms_create_unserialize_allowed_classes_cache($allowed);
}
