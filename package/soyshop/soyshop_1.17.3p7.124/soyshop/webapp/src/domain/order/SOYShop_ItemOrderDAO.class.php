<?php
/**
 * @entity order.SOYShop_ItemOrder
 */
abstract class SOYShop_ItemOrderDAO extends SOY2DAO{

	/**
	 * @trigger onInsert
	 * @return id
	 */
	abstract function insert(SOYShop_ItemOrder $itemOrder);
	abstract function update(SOYShop_ItemOrder $itemOrder);
	abstract function delete(SOYShop_ItemOrder $itemOrder);
	
	/**
	 * @final
	 */
	function onInsert($query, $binds){
		if(!isset($binds[":isAddition"]) || strlen($binds[":isAddition"]) < 1){
			$binds[":isAddition"] = 0;
		}
		return array($query, $binds);
	}

 	/**
 	 * @index id
 	 * @query #orderId# = :orderId
 	 */
	abstract function getByOrderId($orderId);

    /**
     * @return list
     * @order update_date desc
     */
    abstract function get();

 	/**
 	 * @order id desc
 	 * @query #itemId# = :itemId
 	 */
 	abstract function getByItemId($itemId);
 	
 	/**
 	 * @return object
 	 * @query order_id = :orderId AND item_id = :itemId
 	 */
 	abstract function getByOrderIdAndItemId($orderId, $itemId);

    /**
     * @columns item_id, sum(item_count) as item_count
     * @return column_item_count
     * @group item_id
     * @query is_sended = 0 and #itemId# = :itemId and order_id in (select distinct id from soyshop_order where order_status != 1)
     */
    abstract function countByItemId($itemId);

	/**
	 * @columns #orderId#,#isSended#
	 * @query #orderId# = :orderId
	 */
    abstract function updateIsSended($orderId, $isSended);

   /**
     * @columns order_id, sum(total_price) as total_price
     * @return column_total_price
     * @group order_id
     */
    abstract function getTotalPriceByOrderId($orderId);

   /**
     * @columns order_id, sum(item_count) as item_count
     * @return column_item_count
     * @group order_id
     */
    abstract function getTotalItemCountByOrderId($orderId);

}
?>