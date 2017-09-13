<?php

/**
 * @entity SOYShop_UserGroup
 */
abstract class SOYShop_UserGroupDAO extends SOY2DAO {

	/**
	 * @return id
	 * @trigger onUpdate
	 */
	abstract function insert(SOYShop_UserGroup $bean);

	/**
	 * @trigger onUpdate
	 */
	abstract function update(SOYShop_UserGroup $bean);

	/**
	 * @query is_disabled != 1
	 */
	abstract function get();

	/**
	 * @return object
	 */
	abstract function getById($id);

	/**
	 * @final
	 */
	function onUpdate($query, $binds){
		if(!isset($binds[":order"]) || !is_numeric($binds[":order"])) $binds[":order"] = 0;
		if(is_null($binds[":isDisabled"])) $binds[":isDisabled"] = 0;

		return array($query, $binds);
	}
}
