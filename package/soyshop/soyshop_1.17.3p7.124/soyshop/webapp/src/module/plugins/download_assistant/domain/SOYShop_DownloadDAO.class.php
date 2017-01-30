<?php
/**
 * @entity SOYShop_Download
 */
abstract class SOYShop_DownloadDAO extends SOY2DAO{
	
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
   	 * @return list
   	 */
   	abstract function getByOrderId($orderId);
   	
   	/**
   	 * @return list
   	 * @columns item_id
   	 * @query order_id =:orderId
   	 * @distinct true
   	 */
   	abstract function getItemIdByOrderId($orderId);
   	
   	/**
   	 * @return list
   	 * @query order_id = :orderId AND item_id =:itemId
   	 */
   	abstract function getByOrderIdAndItemId($orderId, $itemId);
   	
   	/**
   	 * @return list
   	 */
   	abstract function getByItemId($itemId);
   	
   	/**
   	 * @return list
   	 * @query item_id =:itemId and user_id =:userId
   	 * @distinct true
   	 */
   	abstract function getFilesByItemId($itemId, $userId);
   	
   	/**
   	 * @return list
   	 * @query order_id = :orderId and item_id = :itemId and user_id = :userId
   	 */
   	abstract function getFilesByOrderIdAndItemIdAndUserId($orderId, $itemId, $userId);
   	
   	/**
   	 * @return list
   	 * @order order_date desc
   	 * @limit = 1
   	 */
   	abstract function getByUserId($userId);
   	
   	/**
   	 * @return list
   	 * @query user_id = :userId AND received_date > 0
   	 * @distinct true
   	 */
   	abstract function getFilesByUserId($userId);

   	/**
   	 * @return object
   	 */
   	abstract function getByToken($token);
   	
   	/**
	 * @return id
	 */
   	abstract function insert(SOYShop_Download $bean);
   	
	abstract function update(SOYShop_Download $bean);
}
?>