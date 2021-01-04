<?php
SOY2::import("module.plugins.bulletin_board.domain.SOYBoard_Group");
/**
 * @entity SOYBoard_Group
 */
abstract class SOYBoard_GroupDAO extends SOY2DAO {

	/**
	 * @return id
	 * @trigger onInsert
	 */
	abstract function insert(SOYBoard_Group $bean);

	/**
	 * @trigger onUpdate
	 */
	abstract function update(SOYBoard_Group $bean);

	/**
	 * @query is_disabled = 0
	 * @order display_order ASC
	 */
	abstract function get();

	/**
	 * @return object
	 */
	abstract function getById($id);

	/**
	 * @return object
	 */
	abstract function getByName($name);

	abstract function deleteById($id);

	/**
	 * @final
	 */
	function onInsert($query, $binds){
		list($query, $binds) = self::onUpdate($query, $binds);
		$binds[":createDate"] = time();
		return array($query, $binds);
	}

	/**
	 * @final
	 */
	function onUpdate($query, $binds){
		$binds[":updateDate"] = time();
		if(!isset($binds[":displayOrder"]) || !is_numeric($binds[":displayOrder"])) $binds[":displayOrder"] = SOYBoard_Group::UPPER_LIMIT;
		if(!isset($binds[":isDisabled"]) || !is_numeric($binds[":isDisabled"])) $binds[":isDisabled"] = SOYBoard_Group::NOT_DISABLED;
		return array($query, $binds);
	}
}
