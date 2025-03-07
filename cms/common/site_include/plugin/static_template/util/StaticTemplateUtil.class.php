<?php

class StaticTemplateUtil {

	public static function getTemplateFileNameList(){
		$fs = soy2_scanfiles(self::getTemplateDirectory());
		if(!count($fs)) return array();

		$_arr = array();
		foreach($fs as $f){
			if(preg_match('/\.html$/', $f) !== 1) continue;
			$_ini = str_replace(".html", ".ini", $f);
			if(!file_exists($_ini)) continue;
			
			$_f = trim(substr($f, strrpos($f, "/")), "/");

			$_v = parse_ini_file($_ini);
			$_arr[] = array(
				"filename" => htmlspecialchars($_f,  ENT_QUOTES, "UTF-8"),
				"name" => (isset($_v["name"])) ? htmlspecialchars($_v["name"], ENT_QUOTES, "UTF-8") : "",
				"type" => (isset($_v["type"])) ? htmlspecialchars($_v["type"], ENT_QUOTES, "UTF-8") : "",
			);
		}
		
		return $_arr;
	}

	public static function getTemplateList(){
		return soy2_scanfiles(self::getTemplateDirectory());
	}
	
	public static function getTemplateDirectory(){
		if(defined("_SITE_ROOT_")){
			$dir = _SITE_ROOT_."/";
		}else{
			$dir = UserInfoUtil::getSiteDirectory();
		}
		$dir .= ".static_template/";
		if(!file_exists($dir)) mkdir($dir);
		return $dir;
	}

	/**
	 * @param int
	 * @return string
	 */
	public static function buildFieldId(int $pageId, string $blogPageType=""){
		$fieldId = StaticTemplatePlugin::FIELD_ID."_".(string)$pageId;
		$reqUri = $_SERVER["REQUEST_URI"]; 
		if(soy2_strpos($reqUri, "?") > 0){
			$reqUri = substr($reqUri, 0, strpos($reqUri, "?"));
		}
		if(soy2_strpos($reqUri, "#") > 0){
			$reqUri = substr($reqUri, 0, strpos($reqUri, "#"));
		}
		$_arr = explode("/", $reqUri);
		if(!isset($_arr[count($_arr)-1])) return $fieldId;
	
		if(strlen($blogPageType)) return $fieldId."_".$blogPageType;

		if(defined("_SITE_ROOT_")) return $fieldId;

		$last = $_arr[count($_arr)-1];
		if(is_numeric($last)) return $fieldId;
		return $fieldId."_".$last;
	}
}
