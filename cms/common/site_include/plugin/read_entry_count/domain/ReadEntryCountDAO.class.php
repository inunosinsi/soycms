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

	function getRanking($limit = 5){
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

	function getRankingByLabelIds(array $labelIds, int $blogPageId, $limit = 5){
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

		foreach($results as $key => $result){
			if($blogPageId > 0) $labelIds[] = $blogPageId;
			$results[$key]["labels"] = $labelIds;
		}

		return $results;
	}

	/**
	 * @return object
	 */
	abstract function getByEntryId($entryId);

	/**
	 * @final
	 */
	function onUpdate($query, $binds){
		if(is_null($binds[":count"])) $binds[":count"] = 0;

		return array($query, $binds);
	}
}
