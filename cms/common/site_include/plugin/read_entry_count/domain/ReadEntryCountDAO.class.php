<?php

/**
 * @entity ReadEntryCount
 */
abstract class ReadEntryCountDAO extends SOY2DAO {

	/**
	 * @trigger onUpdate
	 */
	abstract function insert(ReadEntryCount $bean);

	/**
	 * @query entry_id = :entryId
	 * @trigger onUpdate
	 */
	abstract function update(ReadEntryCount $bean);

	/**
	 * @order count DESC
	 */
	abstract function get();

	/**
	 * @final
	 * @param int
	 * @return array
	 */
	function getRanking(int $limit=5){
		SOY2::import("domain.cms.Entry");
		$now = time();
		
		$sql = "SELECT ent.*, cnt.count FROM ReadEntryCount cnt ".
						"INNER JOIN Entry ent ".
						"ON cnt.entry_id = ent.id ".
						"WHERE ent.isPublished = " . Entry::ENTRY_ACTIVE . " ".
						"AND ent.openPeriodStart < " . $now . " ".
						"AND ent.openPeriodEnd > " . $now . " ".
						"ORDER BY cnt.count DESC ".
						"LIMIT " . $limit;
		try{
			$results = $this->executeQuery($sql);
		}catch(Exception $e){
			return array();
		}

		$entryLabelDao = SOY2DAOFactory::create("cms.EntryLabelDAO");
		foreach($results as $key => $result){
			if(!isset($result["id"])) continue;
			try{
				$labels = $entryLabelDao->getByEntryId($result["id"]);
			}catch(Exception $e){
				continue;
			}
			if(!count($labels)) continue;
			$labelList = array();
			foreach($labels as $label){
				$labelList[] = $label->getLabelId();
			}

			$results[$key]["labels"] = $labelList;
		}

		return $results;
	}

	/**
	 * @param array, int, int
	 * @return array
	 */
	function getRankingByLabelIds(array $labelIds, int $blogPageId=0, int $limit=5){
		$sql = "SELECT ent.*, cnt.count FROM ReadEntryCount cnt ".
						"INNER JOIN Entry ent ".
						"ON cnt.entry_id = ent.id ".
						"INNER JOIN EntryLabel lab ".
						"ON ent.id = lab.entry_id ".
						"WHERE lab.label_id IN (" . implode(",", $labelIds) . ") ".
						"ORDER BY cnt.count DESC ".
						"LIMIT " . $limit;
		try{
			$results = $this->executeQuery($sql);
		}catch(Exception $e){
			return array();
		}
		if(!count($results)) return array();

		foreach($results as $key => $result){
			if($blogPageId > 0) $labelIds[] = $blogPageId;
			$results[$key]["labels"] = $labelIds;
		}

		return $results;
	}

	/**
	 * @param int, int
	 * @return array
	 */
	function getRankingByBlogPageId(int $labelId=0, int $limit=5){
		$sql = "SELECT ent.*, cnt.count FROM ReadEntryCount cnt ".
						"INNER JOIN Entry ent ".
						"ON cnt.entry_id = ent.id ".
						"INNER JOIN EntryLabel lab ".
						"ON ent.id = lab.entry_id ".
						"WHERE lab.label_id = :labelId ".
						"ORDER BY cnt.count DESC ".
						"LIMIT " . $limit;
		try{
			$results = $this->executeQuery($sql, array(":labelId" => $labelId));
		}catch(Exception $e){
			return array();
		}
		if(!count($results)) return array();

		foreach($results as $key => $result){
			$results[$key]["labels"][] = $labelId;
		}

		return $results;
	}

	/**
	 * @return object
	 */
	abstract function getByEntryId($entryId);

	/**
	 * @final
	 * @param array
	 * @return array
	 */
	function getCountListByEntryIds(array $entryIds){
		if(!count($entryIds)) return array();

		try{
			$results = $this->executeQuery(
				"SELECT entry_id, count FROM ReadEntryCount ".
				"WHERE entry_id IN (" . implode(",", $entryIds) . ")"
			);
		}catch(Exception $e){
			return array();
		}

		$list = array();
		foreach($results as $res){
			$list[(int)$res["entry_id"]] = (int)$res["count"];
		}

		return $list;;
	}

	/**
	 * @final
	 */
	function onUpdate($query, $binds){
		if(is_null($binds[":count"])) $binds[":count"] = 0;

		return array($query, $binds);
	}
}
