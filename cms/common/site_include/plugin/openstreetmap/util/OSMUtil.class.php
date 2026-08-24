<?php
class OSMUtil {

	const OSM_KEY = "soycms_osm_values";

	/**
	 * アイコンを格納するディレクトリのパスを出力
	 * @return string
	 */
	public static function iconDir(){
		return self::_iconDir();
	}

	/**
	 * 登録済みアイコンを取得する
	 * @return array
	 */
	public static function getRegisteredIcons(){
		return soy2_scandir(self::_iconDir());
	}

	/**
	 * 6文字のハッシュ値を生成
	 * @return string
	 */
	public static function createHash(string $filename){
		return substr(md5($filename), 0, 6);
	}

	/**
	 * @param array
	 * @return array
	 */
	public static function organizeIconConfig(array $cfg){
		if(!count($cfg)) return array();
	
		$icons = self::getRegisteredIcons();
		if(!count($icons)) return array();

		$arr = array();
		foreach($icons as $icon){
			foreach($cfg as $hash => $_arr){
				if(self::createHash($icon) != $hash) continue;
				$_arr["filename"] = $icon;
				$arr[$hash] = $_arr;
			}
		}
		return $arr;
	}

	/**
	 * @param int
	 * @return OpenStreetMap
	 */
	public static function getOSMObjectByEntryId(int $entryId){
		static $dao;
		if(!class_exists("OpenStreetMapDAO")) SOY2::import("site_include.plugin.openstreetmap.domain.OpenStreetMapDAO");
		if(is_null($dao)) $dao = SOY2DAOFactory::create("OpenStreetMapDAO");

		if(isset($GLOBALS[self::OSM_KEY][$entryId]) && is_array($GLOBALS[self::OSM_KEY][$entryId])){
			$GLOBALS[self::OSM_KEY][$entryId]["entryId"] = $entryId;
			return SOY2::cast("OpenStreetMap", $GLOBALS[self::OSM_KEY][$entryId]);
		}else{
			$obj = new OpenStreetMap();
			$obj->setEntryId($entryId);
			return $obj;
		}
	}

	/**
	 * アイコンを格納するディレクトリのパスを出力
	 * @return string
	 */
	private static function _iconDir(){
		SOY2::import("site_include.func.public" ,".php");
		$path = $_SERVER["DOCUMENT_ROOT"]."/".soycms_get_site_id_by_frontcontroller()."/files/";
		if(!file_exists($path)) mkdir($path);
		$path .= ".osm/";
		if(!file_exists($path)) mkdir($path);
		return $path;
	}	
}
