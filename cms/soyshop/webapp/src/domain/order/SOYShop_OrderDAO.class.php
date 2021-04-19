<?php

/**
 * @entity order.SOYShop_Order
 */
abstract class SOYShop_OrderDAO extends SOY2DAO{


	abstract function get();

	/**
	 * @return object
	 */
	abstract function getById($id);

	/**
	 * @order id desc
	 */
	abstract function getByItemId($itemId);

	/**
	 * @order id desc
	 */
	abstract function getByUserId($userId);

	/**
	 * @return list
	 * @query #userId# = :userId AND #status# != 1 AND #status# != 5
	 * @order id desc
	 */
	abstract function getByUserIdIsRegistered($userId);

	/**
	 * @return column_count
	 * @columns count(id) as count
	 * @query #userId# = :userId AND #status# != 1 AND #status# != 5
	 * @order id desc
	 */
	abstract function countByUserIdIsRegistered($userId);

	/**
	 * @return column_count
	 * @columns count(id) as count
	 * @query #userId# = :userId AND #status# = 5
	 * @order id desc
	 */
	abstract function countByUserIdIsCanceled($userId);

	/**
	 * @return object
	 * @query #id# = :id AND #userId# = :userId
	 */
	abstract function getForOrderDisplay($id, $userId);




	/**
	 * @final
	 */
	function getByStatus($status1, $status2 = null){
		if($status2){
			return $this->getByStatusImpl($status1, $status2);
		}else{
			return $this->getByOrderStatus($status1);
		}
	}

	/**
	 * @order id desc
	 * @query #status# = :status1
	 */
	abstract function getByOrderStatus($status1);

	/**
	 * @order id desc
	 * @query #status# = :status1 and #paymentStatus# = :status2
	 */
	abstract function getByStatusImpl($status1, $status2);

	/**
	 * @return object
	 */
	abstract function getByTrackingNumber($trackingNumber);

	/**
	 * @final
	 */
	function getTrackingNumberListByIds($ids){
		if(!is_array($ids) || !count($ids)) return array();

		try{
			$res = $this->executeQuery("SELECT id, tracking_number FROM soyshop_order WHERE id IN (" . implode(",", $ids) . ")");
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return array();

		$list = array();
		foreach($res as $v){
			$list[(int)$v["id"]] = $v["tracking_number"];
		}
		return $list;
	}

	/**
	 * @final
	 */
	function getOrderIdAndUserIdPairList($ids){
		if(!is_array($ids) || !count($ids)) return array();

		try{
			$res = $this->executeQuery("SELECT id, user_id FROM soyshop_order WHERE id IN (" . implode(",", $ids) . ")");
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return array();

		$list = array();
		foreach($res as $v){
			$list[(int)$v["id"]] = (int)$v["user_id"];
		}
		return $list;
	}

	/**
	 * @final
	 */
	function getOrderDateListByIds($ids){
		if(!is_array($ids) || !count($ids)) return array();

		try{
			$res = $this->executeQuery("SELECT id, order_date FROM soyshop_order WHERE id IN (" . implode(",", $ids) . ")");
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return array();

		$list = array();
		foreach($res as $v){
			$list[(int)$v["id"]] = (int)$v["order_date"];
		}
		return $list;
	}

	/**
	 * @trigger onInsert
	 * @return id
	 */
	abstract function insert(SOYShop_Order $order);

	/**
	 * @final
	 */
	function onInsert($query, $binds){
		if(!isset($binds[":orderDate"])) $binds[":orderDate"] = time();
		return array($query, $binds);
	}

	abstract function update(SOYShop_Order $order);

	/**
	 * @final
	 */
	function updateStatus(SOYShop_Order $order){
		$itemOrderDAO = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO");
		$itemOrderDAO->updateIsSended(
			$order->getId(),
			(int)($order->getStatus() == SOYShop_Order::ORDER_STATUS_SENDED)
		);


		$this->update($order);
	}

	/**
	 * 特定の時刻間の注文をすべて取得する
	 * @return list
	 * @query order_date > :startDate AND order_date <= :endDate
	 * @order id ASC
	 */
	abstract function getByBetweenOrderDate($startDate, $endDate = 2147483647);

	/**
	 * @columns mail_status
	 * @query #id# = :id
	 */
	abstract function updateMailStatus(SOYShop_Order $order);

	abstract function deleteAll();

}
