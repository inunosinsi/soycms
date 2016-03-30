<?php
/**
 * @entity order.SOYShop_OrderStateHistory
 */
abstract class SOYShop_OrderStateHistoryDAO extends SOY2DAO{

    /**
	 * @return id
	 */
    abstract function insert(SOYShop_OrderStateHistory $bean);

    /**
     * @order id desc
     */
    abstract function getByOrderId($orderId);
    
    /**
     * @return list
     * @query order_id = :orderId AND order_date > :startDate AND order_date <= :endDate
     * @order id asc
     */
    abstract function getByOrderIdBetweenDate($orderId, $startDate, $endDate = 2147483647);

}
?>