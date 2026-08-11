<?php
SOY2::import("module.plugins.item_review.domain.SOYShop_ReviewPoint");
/**
 * @entity SOYShop_ReviewPoint
 */
abstract class SOYShop_ReviewPointDAO extends SOY2DAO{

	/**
	 * @return id
	 */
	abstract function insert(SOYShop_ReviewPoint $bean);

	abstract function update(SOYShop_ReviewPoint $bean);

	abstract function get();

	/**
	 * @return object
	*/
	abstract function getByReviewId($reviewId);

	abstract function deleteByReviewId($reviewId);
}
