<?php

/**
 * @entity cms.EntryTrackback
 */
abstract class EntryTrackbackDAO extends SOY2DAO{

	/**
	 * @return id
	 */
    abstract function insert(EntryTrackback $bean);
    abstract function delete($id);

    /**
     * @return object
     */
    abstract function getById($id);

    /**
     * @query_type update
     * @columns certification
     * @query id = :id
     *
     */
    abstract function setCertification($id,$certification);

    abstract function getByEntryId($entryId);

    /**
     * @query certification = 1 AND entry_id = :entryId
     * @order #submitdate# DESC
     */
    abstract function getCertificatedTrackbackByEntryId($entryId);

	abstract function get();

    abstract function deleteByEntryId($entryId);

   	/**
	 * @columns count(id) as count
	 */
	function getTrackbackCountByEntryId($entryId){
		$this->setLimit(1);
		$result = $this->executeQuery($this->getQuery(),$this->getBinds());

		if(count($result)<1)return 0;

		return $result[0]["count"];
	}


	/**
	 * @query certification = 1 AND entry_id = :entryId
	 * @columns count(id) as count
	 * @order #submitdate# DESC
	 */
	function getCertificatedTrackbackCountByEntryId($entryId){
		$this->setLimit(1);
		$result = $this->executeQuery($this->getQuery(),$this->getBinds());

		if(count($result)<1)return 0;

		return $result[0]["count"];
	}
}
