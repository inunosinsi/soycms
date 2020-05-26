<?php
SOY2::import("module.plugins.supplier_manager.domain.SOYShop_Supplier");
/**
 * @entity SOYShop_Supplier
 */
abstract class SOYShop_SupplierDAO extends SOY2DAO {

	/**
	 * @return id
	 * @trigger onInsert
	 */
	abstract function insert(SOYShop_Supplier $bean);

	/**
	 * @trigger onUpdate
	 */
	abstract function update(SOYShop_Supplier $bean);

	/**
	 * @order update_date
	 */
	abstract function get();

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
