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
		if(!isset($binds[":status"])) $binds[":status"] = 0;
		if(!isset($binds[":cdate"])) $binds[":cdate"] = time();
		if(!isset($binds[":displayOrder"])) $binds[":displayOrder"] = 0;
		if(!isset($binds[":isAddition"]) || strlen($binds[":isAddition"]) < 1) $binds[":isAddition"] = 0;
		if(!isset($binds[":isConfirm"]) || strlen($binds[":isConfirm"]) < 1) $binds[":isConfirm"] = 0;

		for(;;){
			$i++;
			try{
				$res = $this->executeQuery("SELECT id FROM soyshop_orders WHERE order_id = :orderId AND item_id = :itemId AND cdate = :cdate LIMIT 1;", array(":orderId" => $binds[":orderId"], ":itemId" => $binds[":itemId"], ":cdate" => $binds[":cdate"] + $i));
			}catch(Exception $e){
				//var_dump($e);
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
     * @query is_sended = 0 and #itemId# = :itemId and order_id in (select distinct id from soyshop_order where order_status != 0 AND order_status != 1)
     */
    abstract function countByItemId($itemId);

	/**
	 * @final
	 */
	function countOrderCountListByItemIds($itemIds){
		if(!is_array($itemIds) || !count($itemIds)) return array();
		try{
			$res = $this->executeQuery(
				"SELECT item_id, SUM(item_count) AS item_count ".
					"FROM soyshop_orders ".
					"WHERE is_sended = 0 ".
					"AND item_id IN (" . implode(",", $itemIds) . ") ".
					"AND order_id IN (".
						"SELECT DISTINCT id ".
						"FROM soyshop_order ".
						"WHERE order_status != 0 ".
						"AND order_status != 1".
					") ".
					"GROUP BY item_id"
				);
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return array();

		$list = array();
		foreach($res as $v){
			$list[(int)$v["item_id"]] = (int)$v["item_count"];
		}
		return $list;
	}

	/**
     * @columns sum(item_count) as item_count
     * @return column_item_count
     * @query is_sended = 0 and #itemId# in (SELECT id FROM soyshop_item WHERE item_type = :itemId) and order_id in (select distinct id from soyshop_order where order_status != 0 AND order_status != 1)
     */
	abstract function countChildOrderTotalByItemId($itemId);

	/**
	 * @final
	 */
	function countChildOrderCountListByItemIds($itemIds){
		if(!is_array($itemIds) || !count($itemIds)) return array();
		try{
			$res = $this->executeQuery(
				"SELECT i.item_type, SUM(o.item_count) AS item_count ".
				"FROM soyshop_orders o ".
				"INNER JOIN soyshop_item i ".
				"ON o.item_id = i.id ".
				"WHERE o.is_sended = 0 ".
				"AND i.item_type IN (" . implode(",", $itemIds) . ")".
				"AND o.order_id IN (".
					"SELECT DISTINCT id ".
					"FROM soyshop_order ".
					"WHERE order_status != 0 ".
					"AND order_status != 1".
				") ".
				"GROUP BY i.item_type"
			);
 		}catch(Exception $e){
 			$res = array();
 		}
		if(!count($res)) return array();

		$list = array();
		foreach($res as $v){
			$list[(int)$v["item_type"]] = (int)$v["item_count"];
		}
		return $list;
	}

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

	/**
	 * @final
	 */
	function getItemIdById($id){
		try{
			$res = $this->executeQuery("SELECT item_id FROM soyshop_orders WHERE id = :id LIMIT 1", array(":id" => $id));
		}catch(Exception $e){
			$res = array();
		}

		return (isset($res[0]["item_id"])) ? (int)$res[0]["item_id"] : 0;
	}

}
