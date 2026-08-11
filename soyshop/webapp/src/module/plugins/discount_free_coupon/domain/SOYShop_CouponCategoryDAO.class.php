<?php
SOY2::import("module.plugins.discount_free_coupon.domain.SOYShop_CouponCategory");
/**
 * @entity SOYShop_CouponCategory
 */
abstract class SOYShop_CouponCategoryDAO extends SOY2DAO{

	/**
	 * @index id
	 */
    abstract function get();

	/**
	 * @return object
	 */
   	abstract function getById($id);

   	/**
	 * @return id
	 * @trigger onInsert
	 */
   	abstract function insert(SOYShop_CouponCategory $bean);

   	/**
   	 * @return id
   	 * @trigger onUpdate
   	 */
	abstract function update(SOYShop_CouponCategory $bean);

	abstract function deleteById($id);

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
