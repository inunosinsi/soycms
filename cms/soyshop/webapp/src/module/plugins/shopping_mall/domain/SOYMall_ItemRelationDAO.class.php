<?php
SOY2::import("module.plugins.shopping_mall.domain.SOYMall_ItemRelation");
/**
 * @entity SOYMall_ItemRelation
 */
abstract class SOYMall_ItemRelationDAO extends SOY2DAO {

	abstract function insert(SOYMall_ItemRelation $bean);

	abstract function getByAdminId($adminId);

	/**
	 * @return object
	 * @query item_id = :itemId AND admin_id = :adminId
	 */
	abstract function get($itemId, $adminId);

	abstract function deleteByItemId($itemId);
}
