<?php

class MultiplePageFormUtil {

	const TYPE_TEXT = "text";	//文章のみページ
	const TYPE_CHOICE = "choice";
	const TYPE_FORM = "form";
	const TYPE_EXTEND = "extend";
	const TYPE_CONFIRM = "confirm";
	const TYPE_CONFIRM_CHOICE = "confirm_choice";	//確認 + 選択肢ページ
	const TYPE_COMPLETE = "complete";	//ラストページ

	public static function getTypeList(){
		return self::_types();
	}

	public static function getTypeText($type){
		$types = self::_types();
		return (isset($types[$type])) ? $types[$type] : $types[self::TYPE_CHOICE];
	}

	public static function sortItems($items){
		if(!is_array($items) || !count($items)) return array();
		$sort = array();
		foreach($items as $v){
			$sort[] = (isset($v["order"]) && is_numeric($v["order"])) ? (int)$v["order"] : 999;
		}
		array_multisort($sort, SORT_ASC, $items);
		return $items;
	}

	public static function createHash($len=10){
		return self::_hash($len);
	}

	//ページが一つでもあるか？
	public static function isPage(){
		$files = self::_scanDir();
		if(!count($files)) return false;

		foreach($files as $file){
			if(strpos($file, ".json")) return true;
		}

		return false;
	}

	public static function getPageName($hash){
		$cnf = self::_readJson($hash);
		return (isset($cnf["name"])) ? $cnf["name"] : "";
	}

	//ページ一覧を取得。処理速度が遅かったら早くする方法を検討する	excludeHashはリストから除外するハッシュ
	public static function getPageList($excludeHash=null){
		$files = self::_scanDir();
		if(!count($files)) return array();

		$list = array();
		foreach($files as $file){
			if(!strpos($file, ".json")) continue;
			$hash = str_replace(".json", "", trim(substr($file, strrpos($file, "/")), "/"));
			if(strlen($excludeHash) && $excludeHash == $hash) continue;

			$array = self::_readJson($hash);

			$list[$hash] = array("name" => $array["name"], "type" => $array["type"], "order" => $array["order"]);
			$sort[$hash] = $array["order"];
		}

		//並び順の変更
		if(count($list)) array_multisort($sort, SORT_ASC, $list);

		return $list;
	}

	public static function getPageItemList($excludeHash=null){
		$list = self::getPageList($excludeHash);
		if(!count($list)) return array();
		$array = array();
		foreach($list as $hash => $cnf){
			$array[$hash] = $cnf["name"] . "：" . self::getTypeText($cnf["type"]);
		}

		return $array;
	}

	public static function generateJson($name, $type){
		//作成しているページ数を調べる
		$cnt = self::_countPage();
		$cnf = array("name" => $name, "type" => $type, "order" => ++$cnt);
		$json = json_encode($cnf);
		$filepath = self::_jsonDir() . self::_hash() . ".json";
		file_put_contents($filepath, $json);
	}

	public static function removeJson($hash){
		$file = self::_jsonDir() . $hash . ".json";
		if(file_exists($file)) unlink($file);
	}

	public static function savePageConfig($hash, $cnf){
		$json = json_encode($cnf);
		$filepath = self::_jsonDir() . $hash . ".json";
		file_put_contents($filepath, $json);
	}

	public static function isJson($hash){
		return (file_exists(self::_jsonDir() . $hash . ".json"));
	}

	public static function readJson($hash){
		return self::_readJson($hash);
	}

	public static function jsonDir(){
		return self::_jsonDir();
	}

	public static function getTemplateList($type){
		$list = array();
		$list[] = "default";

		$tmpDir = self::_templateDir($type);
		$files = soy2_scanfiles($tmpDir);
		if(!count($files)) return $list;

		foreach($files as $file){
			$filename = trim(substr($file, strrpos($file, "/")), "/");
			if(!strpos($filename, ".php")) continue;
			$list[] = str_replace(".php", "", $filename);
		}
		return $list;
	}

	public static function getTemplateFilePath($cnf){
		$tmp = (isset($cnf["template"]) && strlen($cnf["template"])) ? $cnf["template"] : "default";
		if($tmp == "default"){
			return self::_defaultTemplateDir() . $cnf["type"] . "/default.php";
		}else{
			return self::_templateDir($cnf["type"]) . $tmp . ".php";
		}
	}

	public static function getDefaultTemplateFilePath($type){
		return self::_defaultTemplateDir() . $type . "/default.php";
	}

	public static function getCustomTemplateFileDir($type){
		return self::_templateDir($type);
	}

	/** private method **/

	private static function _types(){
		return array(
			self::TYPE_TEXT => "テキストのみ",
			self::TYPE_CHOICE => "選択式",
			self::TYPE_FORM => "入力フォーム",
			self::TYPE_EXTEND => "高度なページ",
			self::TYPE_CONFIRM => "確認画面",
			self::TYPE_CONFIRM_CHOICE => "確認 + 選択肢",
			self::TYPE_COMPLETE => "完了画面"
		);
	}

	private static function _countPage(){
		$files = self::_scanDir();
		if(!count($files)) return 0;

		$cnt = 0;
		foreach($files as $file){
			if(strpos($file, ".json")) $cnt++;
		}
		return $cnt;
	}

	private static function _hash($len=10){
		return substr(md5(time()), 0, $len);
	}

	private static function _readJson($hash){
		if(!file_exists(self::_jsonDir() . $hash . ".json")) return array();
		$json = file_get_contents(self::_jsonDir() . $hash . ".json");
		return json_decode($json, true);
	}

	private static function _scanDir(){
		return soy2_scanfiles(self::_jsonDir());
	}

	private static function _jsonDir(){
		//公開側と管理画面側で使用する関数が異なる
		if(defined("_SITE_ROOT_")){
			$dir = _SITE_ROOT_ . "/";
		}else{
			$dir = UserInfoUtil::getSiteDirectory();
		}

		$dir .= ".multiPageForm/";
		self::_createDir($dir);

		$dir .= "json/";
		self::_createDir($dir);

		return $dir;
	}

	private static function _templateDir($type){
		//公開側と管理画面側で使用する関数が異なる
		if(defined("_SITE_ROOT_")){
			$dir = _SITE_ROOT_ . "/";
		}else{
			$dir = UserInfoUtil::getSiteDirectory();
		}

		$dir .= ".multiPageForm/";
		self::_createDir($dir);

		$dir .= "tempate/";
		self::_createDir($dir);

		$dir .= $type . "/";
		self::_createDir($dir);

		return $dir;
	}

	private static function _createDir($dir){
		if(!file_exists($dir)) mkdir($dir);
		if(!file_exists($dir . ".htaccess")) {
			file_put_contents($dir . ".htaccess", "Deny from all");
		}
	}

	private static function _defaultTemplateDir(){
		return dirname(dirname(__FILE__)) . "/template/";
	}
}
