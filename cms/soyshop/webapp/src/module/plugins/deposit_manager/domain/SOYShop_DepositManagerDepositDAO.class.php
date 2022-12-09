<?php
SOY2::import("module.plugins.deposit_manager.domain.SOYShop_DepositManagerDeposit");
/**
 * @entity SOYShop_DepositManagerDeposit
 */
abstract class SOYShop_DepositManagerDepositDAO extends SOY2DAO {

	/**
	 * @return id
	 * @trigger onInsert
	 */
	abstract function insert(SOYShop_DepositManagerDeposit $bean);

	/**
	 * @trigger onUpdate
	 */
	abstract function update(SOYShop_DepositManagerDeposit $bean);

	/**
	 * @return object
	 */
	abstract function getById($id);

	/**
	 * @final
	 */
	function onInsert($query, $binds){
		$binds[":createDate"] = time();
		$binds[":updateDate"] = time();
		return array($query, $binds);
	}

	/**
	 * @final
	 */
	function onUpdate($query, $binds){
		$binds[":updateDate"] = time();
		return array($query, $binds);
	}
}
