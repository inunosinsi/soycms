<?php
/**
 * @entity SOYShop_ReviewPoint
 */
abstract class SOYShop_ReviewPointDAO extends SOY2DAO{

	/**
   	 * @return object
   	 */
   	abstract function getByReviewId($reviewId);
   	
   	/**
	 * @return id
	 */
   	abstract function insert(SOYShop_ReviewPoint $bean);
   	
	abstract function update(SOYShop_ReviewPoint $bean);
	
	abstract function deleteByReviewId($reviewId);
}
?>