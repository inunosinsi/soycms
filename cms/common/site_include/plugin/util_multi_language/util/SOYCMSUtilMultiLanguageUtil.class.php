<?php

class SOYCMSUtilMultiLanguageUtil{
	
	const LANGUAGE_FIELD_KEY = "multi_language_";	// ラベル名の多言語化等で使用
	const LANGUAGE_SITE_NAME_KEY = self::LANGUAGE_FIELD_KEY."site_name_";
	const LANGUAGE_SITE_DESCRIPTION_KEY = self::LANGUAGE_FIELD_KEY."site_description_";

	const LANGUAGE_JP = "jp";
	const LANGUAGE_EN = "en";
	const LANGUAGE_ZH = "zh";
	const LANGUAGE_ZH_TW = "zh-tw";
    const LANGUAGE_KO = "ko";
    const LANGUAGE_ES = "es";
	
	const MODE_PC = "pc";
	const MODE_SMARTPHONE = "smartphone";
	
	const IS_USE = 1;
	const NO_USE = 0;

	/**
	 * @param string
	 * @return string
	 */
	public static function getLanguageLabel(string $lang){
		static $_arr;
		if(is_null($_arr)) $_arr = self::allowLanguages();
		return (isset($_arr[$lang])) ? $_arr[$lang] : "";
	}
	
	public static function allowLanguages(bool $all=true){
		$list = array(
			self::LANGUAGE_JP => "日本語",
			self::LANGUAGE_EN => "英語",
			self::LANGUAGE_ZH => "中国語",
			self::LANGUAGE_ZH_TW => "中国語(繁体字)",
			self::LANGUAGE_KO => "韓国語",
            self::LANGUAGE_ES => "スペイン語"
		);
		
		if(!$all){
			
		}
		
		return $list;
	}

	public static function getLanguageIndex(string $lang){
		$_arr = array(
			self::LANGUAGE_JP,
			self::LANGUAGE_EN,
			self::LANGUAGE_ZH,
			self::LANGUAGE_ZH_TW,
			self::LANGUAGE_KO,
            self::LANGUAGE_ES
		);

		$idx = array_search($lang, $_arr);
		if(!is_numeric($idx)) $idx = 0;
		return $idx;
	}

	public static function getLanguageConst(int $idx){
		$_arr = array(
			self::LANGUAGE_JP,
			self::LANGUAGE_EN,
			self::LANGUAGE_ZH,
			self::LANGUAGE_ZH_TW,
			self::LANGUAGE_KO,
            self::LANGUAGE_ES
		);

		return (isset($_arr[$idx])) ? $_arr[$idx] : self::LANGUAGE_JP;
	}

	/**
	 * @paran UtilMultiLanguagePlugin
	 * @return array
	 */
	public static function getLanguageList(UtilMultiLanguagePlugin $pluginObj){
		$cnfs = $pluginObj->getConfig();
		if(!is_array($cnfs) || !count($cnfs)) return array();

		$_arr = array();
		foreach($cnfs as $lang => $cnf){
			if(!isset($cnf["is_use"]) || (int)$cnf["is_use"] !== 1) continue;
			$_arr[] = $lang;
		}
		return $_arr;
	}

	/**
	 * @paran UtilMultiLanguagePlugin
	 * @return array
	 */
	public static function getLanguagePrefixList(UtilMultiLanguagePlugin $pluginObj){
		$cnfs = $pluginObj->getConfig();
		if(!is_array($cnfs) || !count($cnfs)) return array();

		$_arr = array();
		foreach($cnfs as $lang => $cnf){
			if(!isset($cnf["is_use"]) || (int)$cnf["is_use"] !== 1) continue;
			$_arr[$lang] = $cnf["prefix"];
		}
		return $_arr;
	}

	/**
	 * @return array
	 */
	public static function getLanguagePrefixListWithoutPluginObj(){
		$langPlugin = CMSPlugin::loadPluginConfig("UtilMultiLanguagePlugin");
		if(!$langPlugin instanceof UtilMultiLanguagePlugin) return array();

		return self::getLanguagePrefixList($langPlugin);
	}

	public static function getLanguagePrefix(string $lang){
		$langPlugin = CMSPlugin::loadPluginConfig("UtilMultiLanguagePlugin");
		if(!$langPlugin instanceof UtilMultiLanguagePlugin) return "";

		$cnfs = $langPlugin->getConfig();
		if(!is_array($cnfs) || !count($cnfs)) return "";

		foreach($cnfs as $_lang => $cnf){
			if($_lang != $lang || !isset($cnf["is_use"]) || (int)$cnf["is_use"] !== 1) continue;
			return $cnf["prefix"];
		}

		return "";
	}
}