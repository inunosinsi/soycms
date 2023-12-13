<?php

class TagCloudBlockEntryLogic extends SOY2LogicBase {

	function __construct(){}

	function search(int $labelId, int $wordId, int $count=0){
		static $entries;
		if(is_null($entries)){
			$entries = array();
			$dao = soycms_get_hash_table_dao("entry");

			$sql = "SELECT DISTINCT ent.id, ent.* FROM Entry ent ".
				"JOIN EntryLabel lab ".
				"ON ent.id = lab.entry_id ".
				"JOIN TagCloudLinking lnk ".
				"ON ent.id = lnk.entry_id ".
				"WHERE ent.openPeriodStart <= :now ".
				"AND ent.openPeriodEnd >= :now ".
				"AND ent.isPublished = " . Entry::ENTRY_ACTIVE . " ".
				"AND lab.label_id = :labelId ".
				"AND lnk.word_id = :wordId ".
				"GROUP BY ent.id ".
				"ORDER BY ent.cdate DESC ";


			if($count > 0){
				$sql .= "LIMIT " . $count;

				//ページャ
				$args = self::__getArgs();
				if(count($args)){
					$pageNumber = 0;
					foreach($args as $arg){
						if(soy2_strpos($arg, "page-") === 0){
							$pageNumber = (int)str_replace("page-", "", $arg);
						}
					}
					if($pageNumber > 0){
						$offset = $count * $pageNumber;
						$sql .= " OFFSET " . $offset;
					}
				}
			}

			$binds = array(
				":labelId" => $labelId,
				":wordId" => $wordId,
				":now" => time()
			);

			
			try{
				$results = $dao->executeQuery($sql, $binds);
			}catch(Exception $e){
				return array();
			}

			if(!count($results)) return array();

			foreach($results as $row){
				if(isset($row["id"]) && (int)$row["id"]){
					$entries[$row["id"]] = soycms_set_entry_object($dao->getObject($row));
				}
			}
		}

		return $entries;
	}

	function getTotal(int $labelId=0, int $wordId=0){
		if($labelId === 0 || $wordId === 0) return 0;

		$dao = soycms_get_hash_table_dao("entry");
		$sql = "SELECT COUNT(ent.id) AS TOTAL FROM Entry ent ".
			"JOIN EntryLabel lab ".
			"ON ent.id = lab.entry_id ".
			"JOIN TagCloudLinking lnk ".
			"ON ent.id = lnk.entry_id ".
			"WHERE ent.openPeriodStart <= :now ".
			"AND ent.openPeriodEnd >= :now ".
			"AND ent.isPublished = " . Entry::ENTRY_ACTIVE . " ".
			"AND lab.label_id = :labelId ".
			"AND lnk.word_id = :wordId ";

		$binds = array(
			":labelId" => $labelId,
			":wordId" => $wordId,
			":now" => time()
		);

		try{
			$res = $dao->executeQuery($sql, $binds);
		}catch(Exception $e){
			return 0;
		}

		return (isset($res[0]["TOTAL"])) ? (int)$res[0]["TOTAL"] : 0;
	}

	function getArgs(){
		return self::__getArgs();
	}

	private function __getArgs(){
		if(!isset($_SERVER["PATH_INFO"])) return array();
		//末尾にスラッシュがない場合はスラッシュを付ける
		$pathInfo = rtrim($_SERVER["PATH_INFO"], "/") . "/";
		$argsRaw = rtrim(str_replace("/" . $_SERVER["SOYCMS_PAGE_URI"] . "/", "", $pathInfo), "/");
		return explode("/", $argsRaw);
	}
}
