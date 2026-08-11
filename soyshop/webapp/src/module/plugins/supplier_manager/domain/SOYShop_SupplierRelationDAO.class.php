<?php
SOY2::import("module.plugins.supplier_manager.domain.SOYShop_SupplierRelation");
/**
 * @entity SOYShop_SupplierRelation
 */
abstract class SOYShop_SupplierRelationDAO extends SOY2DAO {

	abstract function insert(SOYShop_SupplierRelation $bean);

	abstract function update(SOYShop_SupplierRelation $bean);

	/**
	 * @return object
	 */
	abstract function getByItemId($itemId);

	/**
	 * @return list
	 */
	abstract function getBySupplierId($supplierId);

	/**
	 * @query supplier_id = :supplierId AND item_id = :itemId
	 */
	abstract function delete($supplierId, $itemId);

	abstract function deleteByItemId($itemId);
}
