<?php
SOY2::import("domain.cms.DataSets");
class SearchBlockUtil {

	CONST ON = 1;

	CONST NORMALIZE_NONE = 0;
	CONST NORMALIZE_HALF_TO_FULL = 1;
	CONST NORMALIZE_FULL_TO_HALF = 2;

	/**
	 * セキュリティ
	 * @param string
	 * @return string
	 */
	public static function sanitize(string $q){
		$cfg = self::getSecurityConfig();
		if(isset($cfg["is_strip_tags"]) && (int)$cfg["is_strip_tags"] === SearchBlockUtil::ON){
			$q = strip_tags($q);
		}

		if(isset($cfg["limit_char"]) && (int)$cfg["limit_char"] > 0){
			if(function_exists("mb_substr") && function_exists("mb_strlen") && mb_strlen($q) > $cfg["limit_char"]){
				$q = mb_substr($q, 0, $cfg["limit_char"]);
			}else if(strlen($q) > $cfg["limit_char"]){
				$q = substr($q, 0, $cfg["limit_char"]);
			}
		}

		// 検索クエリでURLを打ち込まれた時
		if(isset($cfg["deny_url"]) && (int)$cfg["deny_url"] === SearchBlockUtil::ON && filter_var($q, FILTER_VALIDATE_URL)) {
			$q = str_replace(array("https:", "http:"), "", $q);
		}

		// スラッシュ、または.から始まる場合
		if(isset($cfg["deny_path"]) && (int)$cfg["deny_path"] === SearchBlockUtil::ON){
			for(;;){
				if(!strlen($q)) break;
				if($q[0] != "/" && $q[0] != ".") break;

				$q = substr($q, 1);
			}
		}

		$q = htmlspecialchars($q, ENT_QUOTES, "UTF-8");
		return $q;
	}

	/*
	 * 正規化
	 * https://www.php.net/manual/ja/function.mb-convert-kana.php を参考
	 * @param string
	 * @return string
	 */
	public static function normalize(string $q){
		$q = trim($q);
		if(!strlen($q)) return "";

		$q = self::sanitize($q);

		$cnf = self::getNormalizeConfig();
		$katakanaCnf = (isset($cnf["katakana"])) ? (int)$cnf["katakana"] : self::NORMALIZE_NONE;

		// 半角カタカナ → 全角カタカナ
		if($katakanaCnf === self::NORMALIZE_HALF_TO_FULL) $q = self::convert($q, '/[ｦ-ﾟ]*/u', "KV");

		$numberCnf = (isset($cnf["number"])) ? (int)$cnf["number"] : self::NORMALIZE_NONE;

		// 全角数字 → 半角数字
		if($numberCnf === self::NORMALIZE_FULL_TO_HALF) $q = self::convert($q, '/[０-９]*/u', "n");

		// 半角数字 → 全角数字
		if($numberCnf === self::NORMALIZE_HALF_TO_FULL) $q = self::convert($q, '/[0-9]*/u', "N");
		
		return $q;
	}

	/**
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	private static function convert(string $q, string $pattern, string $mode){
		preg_match_all($pattern, $q, $tmps);
		if(!isset($tmps[0]) || !count($tmps[0])) return $q;
		
		foreach($tmps[0] as $tmp){
			$tmp = trim($tmp);
			if(!strlen($tmp)) continue;

			$dist = mb_convert_kana($tmp, $mode);
			$q = str_replace($tmp, $dist, $q);
		}
		
		return $q;
	}

	public static function getNormalizeConfig(){
		return DataSets::get("normalize_config", array(
			"katakana" => self::NORMALIZE_NONE
		));
	}

	public static function saveNormalizeConfig(array $values){
		DataSets::put("normalize_config", $values);
	}

	public static function getSecurityConfig(){
		return DataSets::get("search_block_security", array(
			"is_strip_tags" => self::ON,
			"limit_char" => 30
		));
	}

	public static function saveSecurityConfig(array $values){
		if(!isset($values["is_strip_tags"])) $values["is_strip_tags"] = 0;
		if(!isset($values["deny_url"])) $values["deny_url"] = 0;
		if(!isset($values["deny_path"])) $values["deny_path"] = 0;
		DataSets::put("search_block_security", $values);
	}
}
