<?php

/**
 * @entity cms.EntryComment
 */
abstract class EntryCommentDAO extends SOY2DAO{

	/**
	 * @trigger setupComment
	 */
	abstract function insert(EntryComment $bean);
	
	abstract function update(EntryComment $bean);
	
	abstract function delete($id);
	
	/**
	 * @return object
	 */
	abstract function getById($id);
	
	/**
	 * @order id
	 */
	abstract function getByEntryId($entryId);
	
	/**
	 * @order id
	 * @query ##isApproved## = 1 AND entry_id = :entryId
	 */
	abstract function getApprovedCommentByEntryId($entryId);
	
	abstract function get();
	
	/**
	 * @final
	 */
	function setupComment($query,$binds){
		if(is_null($binds[':isApproved'])){
			$binds[':isApproved'] = 1;
		}
		$binds[':submitDate'] = time();
		return array($query,$binds);
	}
	
	/**
	 * @query_type update
	 * @columns ##isApproved##
	 * @query id = :id
	 */
	abstract function setApproved($id,$isApproved);
	
	abstract function deleteByEntryId($entryId);
	
	/**
	 * @columns count(id) as count
	 */
	function getCommentCountByEntryId($entryId){
		$this->setLimit(1);
		$result = $this->executeQuery($this->getQuery(),$this->getBinds());
		
		if(count($result)<1)return 0;
		
		return $result[0]["count"];
	}
	
	
	/**
	 * @query ##isApproved## = 1 AND entry_id = :entryId
	 * @columns count(id) as count
	 */
	function getApprovedCommentCountByEntryId($entryId){
		$this->setLimit(1);
		$result = $this->executeQuery($this->getQuery(),$this->getBinds());
		
		if(count($result)<1)return 0;
		
		return $result[0]["count"];
	}
	
}
?>