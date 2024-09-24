<?php

class SearchBlockEntryLogic extends SOY2LogicBase {

	function __construct(){}

	/**
	 * @param int, string, int
	 * @return array
	 */
	function search(int $labelId, string $query, int $count=0){
		static $entries;
		if(is_null($entries)){
			$entries = array();

			if(!strlen($query)) return $entries;

			$queries = self::_getRelativeQueries($query);
			if(!count($queries)) return $entries;

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

		$queries = self::_getRelativeQueries($query);
		if(!count($queries)) return 0;

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
		}

		$where[] = "(".implode(" OR ", $qWhere).")";
		$where[] = "entry.isPublished = 1";
		$where[] = "entry.openPeriodEnd >= :now";
		$where[] = "entry.openPeriodStart < :now";
		return implode(" AND ", $where);
	}

	/**
	 * @param string
	 * @retrun array
	 */
	function getRelativeQueries(string $query){
		return self::_getRelativeQueries($query);
	}

	/**
	 * @param string
	 * @retrun array
	 */
	private function _getRelativeQueries(string $query){
		static $queries;
		if(is_array($queries)) return $queries;

		$queries = array($query);

		// Gemini APIで検索の精度を高める
		$logic = SOY2Logic::createInstance("site_include.plugin.soycms_search_block.logic.GeminiSearchLogic");
		$geminiApiKey = $logic->getApiKey();
		
		if(!strlen($geminiApiKey)) return $queries;

		$queries = $logic->getRelativeQueries($query);
		
		return $queries;
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
}
