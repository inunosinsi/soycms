<?php
SOY2::import("module.plugins.access_restriction.domain.SOYShop_AccessRestriction");
/**
 * @entity SOYShop_AccessRestriction
 */
abstract class SOYShop_AccessRestrictionDAO extends SOY2DAO {

	/**
	 * @trigger onInsert
	 */
	abstract function insert(SOYShop_AccessRestriction $bean);

	abstract function getByIpAddress($ipAddress);

	/**
	 * @return object
	 */
	abstract function get($ipAddress, $token);

	/**
	 * @query ip_address = :ipAddress AND token = :token
	 */
	abstract function delete($ipAddress, $token);

	/**
	 * @final
	 */
	function onInsert($query, $binds){
		$binds[":createDate"] = time();
		return array($query, $binds);
	}
}
