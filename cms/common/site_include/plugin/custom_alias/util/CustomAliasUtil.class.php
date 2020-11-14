<?php

class CustomAliasUtil {

	const MODE_MANUAL = 0;	//手動
	const MODE_ID = 1;		//常にIDをエイリアス
	const MODE_HASH = 2;	//常に記事タイトルをhash値
	const MODE_RANDOM = 3;	//エイリアスをランダム値

	const INCLUDE_DIGIT = "digit";	//半角数字を含む
	const INCLUDE_LOWER = "lower";	//半角小文字を含む
	const INCLUDE_UPPER = "upper";	//半角大文字を含む


	public static function getBlogPageById($pageId){
		try{
    		return SOY2DAOFactory::create("cms.BlogPageDAO")->getById($pageId);
    	}catch(Exception $e){
    		return new BlogPage();
    	}
	}

	public static function getEntryById($entryId){
		return self::_getEntryById($entryId);
	}

	public static function getAliasById($entryId){
		$entry = self::_getEntryById($entryId);
		if(is_numeric($entry->getId())){
			return $entry->getAlias();
		}else{
			return $entryId;
		}
	}

	private static function _getEntryById($entryId){
		static $entries;
		if(is_null($entries)) $entries = array();
		if(!isset($entries[$entryId])){
			try{
				$entries[$entryId] = SOY2DAOFactory::create("cms.EntryDAO")->getById($entryId);
			}catch(Exception $e){
				$entries[$entryId] = new Entry();
			}
		}
		return $entries[$entryId];
	}

	public static function generateRandomString(){
		$cnf = self::_getAdvancedConfig(self::MODE_RANDOM);
		$len = (isset($cnf["length"]) && is_numeric($cnf["length"])) ? $cnf["length"] : 12;

		$chars = "";
		if(isset($cnf["include"]) && is_numeric(array_search(self::INCLUDE_DIGIT, $cnf["include"]))) $chars .= "0123456789";
		if(isset($cnf["include"]) && is_numeric(array_search(self::INCLUDE_LOWER, $cnf["include"]))) $chars .= "abcdefghijklmnopqrstuvwxyz";
		if(isset($cnf["include"]) && is_numeric(array_search(self::INCLUDE_UPPER, $cnf["include"]))) $chars .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

		if(!strlen($chars)) return "";

        $charLen = strlen($chars);
		$str = "";
		for(;;){
			$str = "";
	        for ($i = 0; $i < $len; $i++) {
	            $str .= $chars[rand(0, $charLen - 1)];
	        }

			//生成したエイリアスが既に使用されていないか？確認する
			try{
				$entry = SOY2DAOFactory::create("cms.EntryDAO")->getByAlias($str);
			}catch(Exception $e){
				break;
			}
		}

        return $str;
	}

	public static function getAdvancedConfig($mode){
		return self::_getAdvancedConfig($mode);
	}

	private static function _getAdvancedConfig($mode){
		SOY2::import("domain.cms.DataSets");
		return DataSets::get("custom_alias." . $mode . ".config", array());
	}

	public static function saveAdvancedConfig($mode, $values){
		SOY2::import("domain.cms.DataSets");
		DataSets::put("custom_alias." . $mode . ".config", $values);
	}
}
