<?php
SOY2::import("module.plugins.deposit_manager.domain.SOYShop_DepositManagerSubject");
/**
 * @entity SOYShop_DepositManagerSubject
 */
abstract class SOYShop_DepositManagerSubjectDAO extends SOY2DAO {

	/**
	 * @return id
	 * @trigger onUpdate
	 */
	abstract function insert(SOYShop_DepositManagerSubject $bean);

	/**
	 * @trigger onUpdate
	 */
	abstract function update(SOYShop_DepositManagerSubject $bean);

	abstract function get();

	/**
	 * @return object
	 */
	abstract function getById($id);

	abstract function deleteById($id);

	/**
	 * @final
	 */
	function onUpdate($query, $binds){
		if(!isset($binds[":displayOrder"])) $binds[":displayOrder"] = 0;
		return array($query, $binds);
	}
}
