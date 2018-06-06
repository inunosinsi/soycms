<?php

/**
 * @entity cms.EntryHistory
 */
abstract class EntryHistoryDAO extends SOY2DAO{

	/**
	 * @trigger onInsert
	 */
	abstract function insert(EntryHistory $bean);

	abstract function delete($id);

	abstract function deleteByEntryId($entryId);

	/**
	 * @order id desc
	 */
	abstract function getByEntryId($entryId);

	/**
	 * @return column_count
	 * @columns count(id) as count
	 */
	abstract function countByEntryId($entryId);

	/**
	 * @return object
	 * @order id desc
	 */
	abstract function getLatestByEntryId($entryId);

	/**
	 * @return object
	 */
	abstract function getById($id);

	abstract function get();

	/**
	 * @final
	 */
	function onInsert($query,$binds){
		$binds[':userId'] = UserInfoUtil::getUserId();
		$binds[':author'] = UserInfoUtil::getUserName();
		$binds[':cdate'] = time();
		return array($query,$binds);
	}
}
