<?php
SOY2::import("site_include.plugin.ping_new.domain.Ping");
/**
 * @entity Ping
 */
abstract class PingDAO extends SOY2DAO{

	/**
	 * @trigger onInsert
	 */
	abstract function insert(Ping $bean);

	/**
	 * @return object
	 */
	abstract function getByEntryId($entryId);

	/**
	 * @final
	 */
	function onInsert($query, $binds){
		$binds[":sendDate"] = time();
		return array($query, $binds);
	}
}
