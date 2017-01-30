<?php
/**
 * @entity SOYShop_ItemReview
 */
abstract class SOYShop_ItemReviewDAO extends SOY2DAO{

	/**
	 * @index id
	 * @order id desc
	 */
    abstract function get();

	/**
	 * @return object
	 */
   	abstract function getById($id);
   	
   	/**
   	 * @return object
   	 * @query #id# = :id AND #userId# = :userId
   	 */
   	abstract function getByIdAndUserId($id, $userId);
   	
   	/**
   	 * @return list
   	 */
   	abstract function getByItemId($itemId);
   	
   	/**
   	 * @return list
   	 * @query item_id = :itemId AND is_approved = 1
   	 * @order create_date desc
   	 */
   	abstract function getIsApprovedByItemId($itemId);
   	
   	/**
   	 * @return list
   	 * @order create_date desc
   	 */
   	abstract function getByUserId($userId);
   	
   	/**
	 * @return id
	 */
   	abstract function insert(SOYShop_ItemReview $bean);
   	
	abstract function update(SOYShop_ItemReview $bean);

	/**
	 * @columns #id#,#isApproved#
	 */
	abstract function updateIsApproved($id, $isApproved);

	abstract function delete($id);
}
?>