<?php

class FixedFormModuleUtil {

	const PLUGIN_ID = "fixed_form_module";

	public static function saveConfig(array $values){
		SOYShop_DataSets::put(self::PLUGIN_ID . ".config", $values);
	}

	public static function getConfig(){
		return SOYShop_DataSets::get(self::PLUGIN_ID . ".config", array());
	}

	/**
	 * @return array(
	 * 	array("moduleId" => "name")
	 * 	...
	 * )
	 */
	public static function getAllModuleList(){
		$res = array();
		//すべてのモジュールを読み込む
		$dir = SOYSHOP_SITE_DIRECTORY . ".module/";

		$files = soy2_scanfiles($dir);

		foreach($files as $file){
			if(!preg_match('/\.php$/', $file)) continue;

			$moduleId = preg_replace('/^.*\.module\//', "", $file);

			//一個目の/より前はカテゴリ
			$moduleId = preg_replace('/\.php$/', "", $moduleId);
			$moduleId = str_replace("/", ".", $moduleId);
			$name = $moduleId;

			//ini
			$iniFilePath = preg_replace('/\.php$/', ".ini", $file);
			if(file_exists($iniFilePath)){
				$array = @parse_ini_file($iniFilePath);
				if(isset($array["name"])) $name = $array["name"];
			}

			$res[$moduleId] = $name;
		}

		return $res;
	}
}
