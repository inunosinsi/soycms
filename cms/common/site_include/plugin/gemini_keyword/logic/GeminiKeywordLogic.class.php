<?php

class GeminiKeywordLogic extends SOY2LogicBase {

	const CANDIDATE_COUNT = 10;

	function __construct(){
		SOY2::import("site_include.plugin.gemini_keyword.util.GeminiKeywordUtil");
	}

	/**
	 * @param int, int
	 */
	function extractKeywordsAndSave(int $blogPageId, int $entryId){
		if($entryId <= 0) return;
	
		$result = SOY2Logic::createInstance("logic.ai.GeminiApiLogic")->executePrompt(
			GeminiKeywordUtil::buildPrompt($blogPageId, $entryId)
		);

		// ワード、ひらがな、カタカナが改行区切りで取得できる
		$_set = explode("\n", $result);
		if(!count($_set)) return;

		$_dic = array();
		foreach($_set as $s){
			$_arr = explode(",", $s);
			if(count($_arr) !== 3) continue;

			// 整形
			foreach($_arr as $idx => $_v){
				$_v = trim($_v);
				/** 「* キーワード」の形式を整形 **/
				$_v = str_replace("*", "", $_v);

				/**「- キーワード」の形式を整形 **/
				$_res = strpos($_v, "-");
				if(is_numeric($_res) && $_res === 0){
					$_v = trim(substr($_v, 1));
				}

				/** 「数字. キーワード」の形式を整形 **/
				preg_match('/[\d]\.(.*)/', $_v, $tmp);
				if(isset($tmp[1])) $_v = $tmp[1];
				
				$_arr[$idx] = trim($_v);
			}
			
			$_dic[] = $_arr;
		}

		// すべてのキーワードを辞書に登録
		GeminiKeywordUtil::saveDictionary($_dic);

		// キーワードを登録
		GeminiKeywordUtil::saveKeywords($_dic);
		
		// キーワードと記事IDを紐づける
		if(count($_dic)){
			GeminiKeywordUtil::deleteByEntryIds(array($entryId));
			GeminiKeywordUtil::saveRelation($entryId, $_dic);	
		}
		
	}

	/**
	 * 概要が未生成、もしくは生成した概要が古い記事の概要を更新する
	 */
	function update(){
		$blogLabelIds = GeminiKeywordUtil::getBlogPageIds();
		if(!count($blogLabelIds)) return;

		$dao = new SOY2DAO();

		try{
			$res = $dao->executeQuery(
				"SELECT entry_id, label_id FROM EntryLabel ".
				"WHERE label_id IN (".implode(",", $blogLabelIds).") ".
				"AND entry_id NOT IN (".
					"SELECT entry_id FROM GeminiKeywordRelation ".
				") ".
				"ORDER BY entry_id DESC ".
				"LIMIT 1"
			);
		}catch(Exception $e){
			$res = array();
		}
		
		if(isset($res[0]["entry_id"]) && is_numeric($res[0]["entry_id"])){
			$entryId = (int)$res[0]["entry_id"];
			$blogLabelId = array_search($res[0]["label_id"], $blogLabelIds);
			self::extractKeywordsAndSave($blogLabelId, $entryId);
			return;
		}		
	}

	/** 検索 **/
	
	/**
	 * @param int, string, int
	 * @return array
	 */
	function search(int $labelId, string $query, int $count=0){
		static $entries;
		if(is_null($entries)){
			$entries = array();

			if(!strlen($query)) return $entries;

			$queries = array($query);

			$binds = array(":label_id" => $labelId, ":now" => time());
					
			$sql = "SELECT DISTINCT entry.id, entry.* FROM Entry entry ".
				 "INNER JOIN EntryLabel label ".
				 "ON entry.id = label.entry_id ".
				 "WHERE ".self::_buildWhere($queries)." ".
				 "ORDER BY entry.cdate desc ";

			foreach($queries as $idx => $query){
				$binds[":query".$idx] = "%".trim($query)."%";
			}

			if($count > 0){
				$sql .= "LIMIT " . $count;

				//ページャ
				$args = self::__getArgs();
				if(isset($args[0]) && strpos($args[0], "page-") === 0){
					$pageNumber = (int)str_replace("page-", "", $args[0]);
					if($pageNumber > 0) $sql .= " OFFSET " . (($count * $pageNumber) - 1);
				}
			}

			$dao = soycms_get_hash_table_dao("entry");

			try{
				$results = $dao->executeQuery($sql, $binds);
			}catch(Exception $e){
				return array();
			}
			
			if(!count($results)) return array();

			foreach($results as $key => $row){
				if(isset($row["id"]) && (int)$row["id"]){
					$entries[$row["id"]] = soycms_set_entry_object($dao->getObject($row));
				}
			}
		}

		return $entries;
	}

	/**
	 * @param int, string
	 * @return int
	 */
	function getTotal(int $labelId, string $query=""){
		if($labelId <= 0 || !strlen($query)) return 0;

		$queries = array($query);

		$binds = array(":label_id" => $labelId, ":now" => time());
		
		$sql = "SELECT COUNT(*) AS TOTAL FROM Entry entry ".
			 "INNER JOIN EntryLabel label ".
			 "ON entry.id = label.entry_id ".
			 "WHERE ".self::_buildWhere($queries);

		foreach($queries as $idx => $query){
			$binds[":query".$idx] = "%".trim($query)."%";
		}

		try{
			$res = soycms_get_hash_table_dao("entry")->executeQuery($sql, $binds);
		}catch(Exception $e){
			return 0;
		}

		return (isset($res[0]["TOTAL"])) ? (int)$res[0]["TOTAL"] : 0;
	}

	/**
	 * @param array
	 * @return string
	 */
	private function _buildWhere(array $queries){
		$where = array();
		$where[] = "label.label_id = :label_id";


		$qWhere = array();
		foreach($queries as $idx => $query){
			$qWhere[] = "(entry.title LIKE :query".$idx." OR entry.content LIKE :query".$idx." OR entry.more LIKE :query".$idx.")";
			$qWhere[] = "entry.id IN (".
							"SELECT r.entry_id FROM GeminiKeywordRelation r ".
							"INNER JOIN GeminiKeyword k ".
							"ON r.keyword_id = k.id ".
							"WHERE k.keyword_id IN (".
								"SELECT id FROM GeminiKeywordDictionary ".
								"WHERE keyword LIKE :query".$idx.
							")".
							"OR k.hiragana_id IN (".
								"SELECT id FROM GeminiKeywordDictionary ".
								"WHERE keyword LIKE :query".$idx.
							")".
							"OR k.katakana_id IN (".
								"SELECT id FROM GeminiKeywordDictionary ".
								"WHERE keyword LIKE :query".$idx.
							")".
						")";
		}

		$where[] = "(".implode(" OR ", $qWhere).")";		
		$where[] = "entry.isPublished = 1";
		$where[] = "entry.openPeriodEnd >= :now";
		$where[] = "entry.openPeriodStart < :now";
		return implode(" AND ", $where);
	}

	function getArgs(){
		return self::__getArgs();
	}

	/**
	 * @return array
	 */
	private function __getArgs(){
		if(!isset($_SERVER["PATH_INFO"])) return array();
		//末尾にスラッシュがない場合はスラッシュを付ける
		$pathInfo = $_SERVER["PATH_INFO"];
		if(strrpos($pathInfo, "/") !== strlen($pathInfo) - 1){
			$pathInfo .= "/";
		}
		$argsRaw = rtrim(str_replace("/" . $_SERVER["SOYCMS_PAGE_URI"] . "/", "", $pathInfo), "/");
		return explode("/", $argsRaw);
	}

	/** キーワードの候補 **/

	/**
	 * @param string
	 * @return array
	 */
	function getCandidateKeywords(string $q){
		$_arr = array();
		$dao = new SOY2DAO();

		try{
			$res = $dao->executeQuery(
				"SELECT DISTINCT keyword FROM GeminiKeywordDictionary ".
				"WHERE keyword LIKE :q ".
				"LIMIT ".self::CANDIDATE_COUNT,
				array(":q" => $q."%")
			);
		}catch(Exception $e){
			$res = array();
		}

		if(count($res)){
			foreach($res as $v){
				if(count($_arr) >= self::CANDIDATE_COUNT) break;
				$k = trim($v["keyword"]);
				if(is_numeric(array_search($k, $_arr))) continue;
				$_arr[] = $k;
			}
		}

		if(count($_arr) >= self::CANDIDATE_COUNT) return $_arr;
		
		try{
			$res = $dao->executeQuery(
				"SELECT DISTINCT keyword FROM GeminiKeywordDictionary ".
				"WHERE keyword LIKE :q ".
				"AND keyword NOT LIKE :notq ".
				"LIMIT ".self::CANDIDATE_COUNT,
				array(
					":q" => "%".$q."%",
					":notq" => $q."%"
				)
			);
		}catch(Exception $e){
			$res = array();
		}

		if(count($res)){
			foreach($res as $v){
				if(count($_arr) >= self::CANDIDATE_COUNT) break;
				$k = trim($v["keyword"]);
				if(is_numeric(array_search($k, $_arr))) continue;
				$_arr[] = $k;
			}
		}


		return $_arr;
	}
}
