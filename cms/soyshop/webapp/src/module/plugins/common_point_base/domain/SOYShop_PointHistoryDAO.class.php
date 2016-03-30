<?php
/**
 * @entity SOYShop_PointHistory
 */
abstract class SOYShop_PointHistoryDAO extends SOY2DAO{
   	/**
	 * @return id
	 */
   	abstract function insert(SOYShop_PointHistory $bean);
   	
	abstract function update(SOYShop_PointHistory $bean);
	
	/**
	 * @return list
	 * @order create_date desc
	 */
	abstract function getByUserId($userId);
	
	/**
	 * @return list
	 * @order create_date desc
	 */
	abstract function getByOrderId($orderId);
	
	/**
	 * @return list
	 * @query user_id = :userId AND order_id = :orderId
	 * @order create_date desc
	 */
	abstract function getByUserIdAndOrderId($userId, $orderId);
}
?>