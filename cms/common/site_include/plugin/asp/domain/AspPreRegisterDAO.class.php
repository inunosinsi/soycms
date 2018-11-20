<?php
SOY2::import("site_include.plugin.asp.domain.AspPreRegister");
/**
 * @entity AspPreRegister
 */
abstract class AspPreRegisterDAO extends SOY2DAO {

	/**
	 * @trigger onUpdate
	 */
	abstract function insert(AspPreRegister $bean);

	/**
	 * @query token = :token
	 * @trigger onUpdate
	 */
	abstract function update(AspPreRegister $bean);

	/**
	 * @return object
	 */
	abstract function getByToken($token);

	abstract function deleteByToken($token);

	/**
	 * @final
	 */
	function onUpdate($query, $binds){
		$binds[":createDate"] = time();

		return array($query, $binds);
	}
}
