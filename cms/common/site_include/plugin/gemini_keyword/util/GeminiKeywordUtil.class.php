<?php

class GeminiKeywordUtil {

	const FIELD_ID = "gemini_keyword";
	const GLOBAL_INDEX = self::FIELD_ID."_list";
	const DATA_SET_KEY = "gemini_keyword_key";

	/**
	 * job中でも利用出来るように$dao->executeQueryでデータを取得する
	 * @return array(pageId => labelId)
	 */
	public static function getBlogPageIds(){
		SOY2::import("domain.cms.Page");
		$dao = new SOY2DAO();

		try{
			$res = $dao->executeQuery(
				"SELECT id, page_config FROM Page ".
				"WHERE page_type = ".Page::PAGE_TYPE_BLOG . " ".
				"AND isPublished = ".Page::PAGE_ACTIVE
			);
		}catch(Exception $e){
			$res = array();
		}
		
		if(!count($res)) return array();

		// 自動生成を有効にするブログページの設定
		$chks = GeminikeywordUtil::getEnabledBlogPages();
		
		$blogLabelIds = array();
		foreach($res as $v){
			$cnf = soy2_unserialize($v["page_config"]);
			if(!property_exists($cnf, "blogLabelId") || !is_numeric($cnf->blogLabelId) || is_bool(array_search($cnf->blogLabelId, $chks))) continue;
			$blogLabelIds[(int)$v["id"]] = (int)$cnf->blogLabelId;
		}
		
		return $blogLabelIds;
	}

	/**
	 * @param int
	 * @return int
	 */
	public static function getBlogPageIdByEntryId(int $entryId){
		$blogLabelIds = self::getBlogPageIds();
		if(!count($blogLabelIds)) return 0;

		try{
			$entryLabels = soycms_get_hash_table_dao("entry_label")->getByEntryId($entryId);
		}catch(Exception $e){
			$entryLabels = array();
		}
		if(!count($entryLabels)) return 0;

		foreach($entryLabels as $entryLabel){
			$labelId = (int)$entryLabel->getLabelId();

			$blogPageId = array_search($labelId, $blogLabelIds);
			if(is_numeric($blogPageId)) return $blogPageId;
		}

		return 0;
	}

	/**
	 * @param int, int
	 * @return array
	 */
	public static function buildPrompt(int $blogPageId, int $entryId){
		$blogPage = soycms_get_hash_table_dao("blog_page")->getById($blogPageId);
		$entry = soycms_get_hash_table_dao("entry")->getById($entryId);
		
		$content = "<h1>".$entry->getTitle()."</h1>";
		$content .= $entry->getContent();
		$content .= $entry->getMore();
		return $content."の内容から検索用のキーワードを取得して一覧にしてください。".
			"各キーワードの読み方のひらがなとカタカナも追加してください。".
			"結果はキーワード、ひらがな、カタカナの順でカンマ区切り。".
			"キーワードは重要度が高い順。";
	}

	/**
	 * @param array
	 */
	public static function saveDictionary(array $dic){
		if(!count($dic)) return;
		$keywords = array();
		foreach($dic as $_arr){
			foreach($_arr as $_k){
				$_k = str_replace("*", "", $_k);
				$keywords[] = trim($_k);
			}
		}

		SOY2::import("site_include.plugin.gemini_keyword.domain.GeminiKeywordDictionaryDAO");
		$dao = SOY2DAOFactory::create("GeminiKeywordDictionaryDAO");	
		$list = $dao->getKeywordList($keywords);

		// 時間を要するけど、丁寧に一つずつインサートしていく
		foreach($keywords as $keyword){
			if(is_numeric(array_search($keyword, $list))) continue;
		
			$obj = new GeminiKeywordDictionary();
			$obj->setKeyword($keyword);
			try{
				$dao->insert($obj);
			}catch(Exception $e){
				//
			}
		}
	}

	/**
	 * @param array
	 * @return array
	 */
	public static function getKeywordDictionary(array $dic){
		if(!count($dic)) return array();
		$keywords = array();
		foreach($dic as $_arr){
			foreach($_arr as $_k){
				$keywords[] = trim($_k);
			}
		}

		SOY2::import("site_include.plugin.gemini_keyword.domain.GeminiKeywordDictionaryDAO");
		return SOY2DAOFactory::create("GeminiKeywordDictionaryDAO")->getKeywordList($keywords);
	}

	/**
	 * @param array
	 */
	public static function saveKeywords(array $dic){
		if(!count($dic)) return;
	
		$list = GeminiKeywordUtil::getKeywordDictionary($dic);
		if(!count($list)) return;

		$pdo = new PDO(
			SOY2DAOConfig::Dsn(),
			SOY2DAOConfig::user(),
			SOY2DAOConfig::pass()
		);
		
		$pdo->beginTransaction();
		$stmt = $pdo->prepare("INSERT INTO GeminiKeyword(keyword_id, hiragana_id, katakana_id) VALUES(:keyword_id, :hiragana_id, :katakana_id)");

		foreach($dic as $d){
			if(!isset($d[0])) continue;
			$keywordId = array_search($d[0], $list);
			if(!is_numeric($keywordId)) continue;

			if(!isset($d[1])) continue;
			$hiraganaId = array_search($d[1], $list);
			if(!is_numeric($hiraganaId)) continue;

			if(!isset($d[2])) continue;
			$katakanaId = array_search($d[2], $list);
			if(!is_numeric($katakanaId)) continue;

			try{
				$stmt->execute(array(
					":keyword_id" => $keywordId,
					":hiragana_id" => $hiraganaId,
					":katakana_id" => $katakanaId 
				));
			}catch(Exception $e){
				//			
			}
		}
		$pdo->commit();
		$pdo = null;
	}

	/**
	 * @param int, array
	 */
	public static function saveRelation(int $entryId, array $dic){
		if(!count($dic)) return;
		$keywords = array();
		foreach($dic as $d){
			if(!isset($d[0])) continue;
			$keywords[] = trim($d[0]);
		}
		
		SOY2::import("site_include.plugin.gemini_keyword.domain.GeminiKeywordDAO");
		$list = SOY2DAOFactory::create("GeminiKeywordDAO")->getIdsByKeywords($keywords);
		if(!count($list)) return;

		$pdo = new PDO(
			SOY2DAOConfig::Dsn(),
			SOY2DAOConfig::user(),
			SOY2DAOConfig::pass()
		);
		
		$pdo->beginTransaction();
		$stmt = $pdo->prepare("INSERT INTO GeminiKeywordRelation(entry_id, keyword_id, importance) VALUES(:entry_id, :keyword_id, :importance)");

		$importance = 1;
		foreach($list as $id => $keyword){
			try{
				$stmt->execute(array(
					":entry_id" => $entryId,
					":keyword_id" => $id,
					":importance" => $importance++
				));
			}catch(Exception $e){
				//			
			}
		}
		$pdo->commit();
		$pdo = null;
	}

	/**
	 * @param array
	 */
	public static function deleteByEntryIds(array $entryIds){
		if(!count($entryIds)) return;
	
		SOY2::import("site_include.plugin.gemini_keyword.domain.GeminiKeywordRelationDAO");
		$dao = SOY2DAOFactory::create("GeminiKeywordRelationDAO");
		foreach($entryIds as $entryId){
			try{
				$dao->deleteByEntryId((int)$entryId);
			}catch(Exception $e){
				//
			}
		}
	}

	/**
	 * @param int
	 * @return array
	 */
	public static function getKeywordsByEntryId(int $entryId){
		SOY2::import("site_include.plugin.gemini_keyword.domain.GeminiKeywordRelationDAO");
		return SOY2DAOFactory::create("GeminiKeywordRelationDAO")->getByEntryId($entryId);
	}


	public static function saveEnabledBlogPages(array $chks){
		SOY2::import("domain.cms.DataSets");
		$v = (count($chks)) ? soy2_serialize($chks) : "";
		DataSets::put(self::DATA_SET_KEY.".enabled", $v);
	}

	public static function getEnabledBlogPages(){
		SOY2::import("domain.cms.DataSets");
		$v = DataSets::get(self::DATA_SET_KEY.".enabled", "");
		return (strlen($v)) ? soy2_unserialize($v) : array();
	}
}
