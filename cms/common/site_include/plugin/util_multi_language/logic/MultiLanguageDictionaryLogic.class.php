<?php

class MultiLanguageDictionaryLogic extends SOY2LogicBase {

	/**
	 * array(
	 *  記事名やキャプションのハッシュ値 => array(
	 * 		多言語のprifix => 値
	 * 	)
	 * )
	 */
	private $dic = array();

	function __construct(){
		SOY2::import("site_include.plugin.util_multi_language.util.SOYCMSUtilMultiLanguageUtil");
		SOY2::import("site_include.plugin.util_multi_language.domain.MultiLanguageEntryRelationDAO");
		SOY2::import("site_include.plugin.util_multi_language.domain.MultiLanguageLabelRelationDAO");
	}

	function buildDictionary(string $lang=SOYCMSUtilMultiLanguageUtil::LANGUAGE_JP){
		self::_buildJapaneseDictionary();
		if(count($this->dic) && $lang != SOYCMSUtilMultiLanguageUtil::LANGUAGE_JP) self::_buildMultiLanguageDictionary($lang);
	}

	private function _buildJapaneseDictionary(){
		$dao = new SOY2DAO();

		try{
			$res = $dao->executeQuery("SELECT * FROM MultiLanguageEntryRelation");
		}catch(Exception $e){
			$res = array();
		}
		
		if(count($res)) {
			$_ids = array();
			foreach($res as $v){
				if(is_bool(array_search($v["parent_entry_id"], $_ids))) $_ids[] = $v["parent_entry_id"];
				if(is_bool(array_search($v["child_entry_id"], $_ids))) $_ids[] = $v["child_entry_id"];
			}

			$list = self::_getEntryList($_ids);
			if(count($list)){
				foreach($res as $v){
					if(!isset($list[$v["parent_entry_id"]]) || !isset($list[$v["child_entry_id"]])) continue;
					$hash = self::_hash($list[$v["parent_entry_id"]]);
					if(!isset($this->dic[$hash])) {
						$this->dic[$hash] = array();
						$this->dic[$hash][SOYCMSUtilMultiLanguageUtil::LANGUAGE_JP] = $list[$v["parent_entry_id"]];
					}

					$lang = SOYCMSUtilMultiLanguageUtil::getLanguageConst($v["lang"]);
					if($lang == SOYCMSUtilMultiLanguageUtil::LANGUAGE_JP) continue;

					$this->dic[$hash][$lang] = $list[$v["child_entry_id"]];
				}
			}
		}

		try{
			$res = $dao->executeQuery("SELECT * FROM MultiLanguageLabelRelation");
		}catch(Exception $e){
			$res = array();
		}
		
		if(count($res)) {
			$_ids = array();
			foreach($res as $v){
				if(is_bool(array_search($v["parent_label_id"], $_ids))) $_ids[] = $v["parent_label_id"];
				if(is_bool(array_search($v["child_label_id"], $_ids))) $_ids[] = $v["child_label_id"];
			}

			$list = self::_getLabelList($_ids);
			if(count($list)){
				foreach($res as $v){
					if(!isset($list[$v["parent_label_id"]]) || !isset($list[$v["child_label_id"]])) continue;
					$hash = self::_hash($list[$v["parent_label_id"]]);
					if(!isset($this->dic[$hash])) {
						$this->dic[$hash] = array();
						$this->dic[$hash][SOYCMSUtilMultiLanguageUtil::LANGUAGE_JP] = $list[$v["parent_label_id"]];
					}

					$lang = SOYCMSUtilMultiLanguageUtil::getLanguageConst($v["lang"]);
					if($lang == SOYCMSUtilMultiLanguageUtil::LANGUAGE_JP) continue;

					$this->dic[$hash][$lang] = $list[$v["child_label_id"]];
				}
			}
		}
	}

	// 配列の並べ替え
	private function _buildMultiLanguageDictionary(string $lang){
		$dic = $this->dic;
		$this->dic = array();
		
		foreach($dic as $jpHash => $list){
			if(!isset($list[$lang])) continue;
			$this->dic[self::_hash($list[$lang])] = $list;
		}
	}

	/**
	 * @param staring, string
	 * @return string
	 */
	function get(string $str, string $lang){
		if(isset($this->dic[self::_hash($str)][$lang])) return $this->dic[self::_hash($str)][$lang];

		// urlencodeしている可能性がある。
		$str = rawurldecode($str);
		return (isset($this->dic[self::_hash($str)][$lang])) ? $this->dic[self::_hash($str)][$lang] : "";
	}

	private function _getLabelList(array $labelIds){
		if(!count($labelIds)) return array();
		try{
			$res = soycms_get_hash_table_dao("label")->executeQuery(
				"SELECT id, alias FROM Label ".
				"WHERE id IN (".implode(",", $labelIds).")"
			);
		}catch(Exception $e){
			$res = array();
		}
		
		if(!count($res)) return array();

		$_arr = array();
		foreach($res as $v){
			$_arr[(int)$v["id"]] = $v["alias"];
		}

		return $_arr;
	}

	private function _getEntryList(array $entryIds){
		if(!count($entryIds)) return array();
		try{
			$res = soycms_get_hash_table_dao("entry")->executeQuery(
				"SELECT id, alias FROM Entry ".
				"WHERE id IN (".implode(",", $entryIds).")"
			);
		}catch(Exception $e){
			$res = array();
		}
		
		if(!count($res)) return array();

		$_arr = array();
		foreach($res as $v){
			$_arr[(int)$v["id"]] = $v["alias"];
		}

		return $_arr;
	}

	/**
	 * @param string
	 * @return string
	 */
	private function _hash(string $str){
		return strlen($str).substr(md5($str), 0, 6);
	}
}