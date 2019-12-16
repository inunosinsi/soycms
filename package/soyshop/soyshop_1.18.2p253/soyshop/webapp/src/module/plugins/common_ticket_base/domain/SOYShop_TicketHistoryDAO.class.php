<?php
/**
 * @entity SOYShop_TicketHistory
 */
abstract class SOYShop_TicketHistoryDAO extends SOY2DAO{
   	/**
	 * @return id
	 * @trigger onInsert
	 */
   	abstract function insert(SOYShop_TicketHistory $bean);

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

	/**
	 * @final
	 */
	function onInsert($query, $binds){
		$binds[":createDate"] = time();
		return array($query, $binds);
	}
}
