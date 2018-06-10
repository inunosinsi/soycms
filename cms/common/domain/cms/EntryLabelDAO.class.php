<?php

/**
 * @entity cms.EntryLabel
 */
abstract class EntryLabelDAO extends SOY2DAO{

	abstract function insert(EntryLabel $bean);

	abstract function update(EntryLabel $bean);
	abstract function deleteByEntryId($entryId);
	abstract function deleteByLabelId($labelId);

	/**
	 * @query #labelId# = :labelId and #entryId# = :entryId
	 */
	abstract function deleteByParams($entryId,$labelId);

	/**
	 * @index labelId
	 */
	abstract function getByEntryId($entryId);

	abstract function getByLabelId($labelId);

	/**
	 * @return column_count_id
	 * @columns count(entry_id) as count_id
	 * @query ##labelId## = :labelId
	 */
	abstract function countByLabelId($labelId);

	/**
	 * @return object
	 * @query #entryId# = :entryId AND #labelId# = :labelId
	 */
	abstract function getByEntryIdLabelId($entryId,$labelId);

	abstract function get();

	/**
	 * @query #labelId# = :labelId AND #entryId# =:entryId
	 * @return object
	 */
	abstract function getByParam($labelId,$entryId);

	/**
	 * @final
	 */
	function setByParams($entryId,$labelId,$displayOrder = null){
		$obj = new EntryLabel();

		$obj->setEntryId($entryId);
		$obj->setLabelId($labelId);
		$obj->setMaxDisplayOrder();

		try{
			$currentObj = $this->getByParam($labelId,$entryId);
			//do noting
		}catch(Exception $e){
			$this->insert($obj);
		}

		if($displayOrder){
			$this->updateDisplayOrder($entryId,$labelId,$displayOrder);
		}
	}

	/**
	 * @distinct
	 * @columns ##entryId#
	 * @distinct
	 * @query ##labelId## in (<?php implode(',',:labelids) ?>)
	 * @group #entryId#
	 * @having count(#entryId#) = <?php count(:labelids) ?>
	 */
	function getNarrowLabels($labelids){

		$tmpQuery = array();
		$binds = array();

		$query = $this->getQuery();

		try{
			$result = $this->executeQuery($query,$binds);
		}catch(Exception $e){
			$result = array();
		}

		$entryIds = array();
		foreach($result as $row){
			$entryIds[] = $row["entry_id"];
		}

		if(empty($entryIds))return array();

		$sql = "select distinct label_id from EntryLabel where entry_id in (".implode(",",$entryIds).")";

		$result = $this->executeQuery($sql,$binds);

		$array = array();
		foreach($result as $row){
			$obj = $this->getObject($row);
			$array[$obj->getLabelId()] = $obj;
		}

		return $array;

	}

	/**
	 * @query #labelId# = :labelId AND #entryId# =:entryId
	 */
	abstract function updateDisplayOrder($entryId,$labelId,$displayOrder);
}
