<?php
SOY2::import("site_include.plugin.asp_app.domain.AspAppPreRegister");
/**
 * @entity AspAppPreRegister
 */
abstract class AspAppPreRegisterDAO extends SOY2DAO {

	/**
	 * @trigger onUpdate
	 */
	abstract function insert(AspAppPreRegister $bean);

	/**
	 * @query token = :token
	 * @trigger onUpdate
	 */
	abstract function update(AspAppPreRegister $bean);

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
