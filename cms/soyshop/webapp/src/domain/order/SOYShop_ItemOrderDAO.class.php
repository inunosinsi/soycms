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
		static $i;
		if(is_null($i)) $i = 0;
		if(!isset($binds[":cdate"])) $binds[":cdate"] = time();
		if(!isset($binds[":displayOrder"])) $binds[":displayOrder"] = 0;
		if(!isset($binds[":isAddition"]) || strlen($binds[":isAddition"]) < 1) $binds[":isAddition"] = 0;

		for(;;){
			$i++;
			try{
				$res = $this->executeQuery("SELECT id FROM soyshop_orders WHERE order_id = :orderId AND item_id = :itemId AND cdate = :cdate LIMIT 1;", array(":orderId" => $binds[":orderId"], ":itemId" => $binds[":itemId"], ":cdate" => $binds[":cdate"] + $i));
			}catch(Exception $e){
				$res = array();
			}

			if(!count($res)) break;
		}
		$binds[":cdate"] += $i;

		return array($query, $binds);
	}

	/**
	 * @return object
	 */
	abstract function getById($id);

 	/**
 	 * @index id
 	 * @query #orderId# = :orderId
	 * @order display_order ASC, id ASC
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
