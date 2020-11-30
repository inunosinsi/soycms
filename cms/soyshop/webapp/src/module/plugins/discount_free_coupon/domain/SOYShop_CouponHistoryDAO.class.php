<?php
SOY2::import("module.plugins.discount_free_coupon.domain.SOYShop_CouponHistory");
/**
 * @entity SOYShop_CouponHistory
 */
abstract class SOYShop_CouponHistoryDAO extends SOY2DAO{

	/**
	 * @return list
	 * @order create_date desc
	 */
    abstract function get();

   	/**
   	 * @return list
   	 */
   	abstract function getByOrderId($orderId);

   	/**
   	 * @return list
   	 * @order order_date desc
   	 * @limit = 1
   	 */
   	abstract function getByUserId($userId);

   	function getByDate($start, $end, $limit=10000){
   		$sql = "SELECT * ".
   				"FROM soyshop_coupon_history ".
   				"WHERE create_date >= :start ".
   				"AND create_date <= :end ".
   				"ORDER BY create_date DESC ".
   				"LIMIT " . $limit;
   		$binds = array(":start" => $start, ":end" => $end);

   		try{
   			$results = $this->executeQuery($sql, $binds);
   		}catch(Exception $e){
   			return array();
   		}

   		if(count($results) === 0) return array();

   		$histories = array();
   		foreach($results as $result){
   			$histories[] = $this->getObject($result);
   		}

   		return $histories;
   	}

	function countByCouponId($couponId){
		SOY2::import("domain.order.SOYShop_Order");

		$sql = "SELECT COUNT(order_id) ".
				"FROM soyshop_coupon_history h ".
				"INNER JOIN soyshop_order o ".
				"ON h.order_id = o.id ".
				"WHERE h.coupon_id = :couponId ".
				"AND o.order_status != ". SOYShop_Order::ORDER_STATUS_CANCELED;

		try{
			$results = $this->executeQuery($sql, array(":couponId" => $couponId));
		}catch(Exception $e){
			return 0;

		}

		return (isset($results[0]["COUNT(order_id)"])) ? (int)$results[0]["COUNT(order_id)"] : 0;
	}

   	/**
	 * @return id
	 * @trigger onInsert
	 */
   	abstract function insert(SOYShop_CouponHistory $bean);

   	/**
   	 * @final
   	 */
   	function onInsert($query, $binds){

   		$binds[":createDate"] = time();
		if(!isset($binds[":isFreeDelivery"]) || is_null($binds[":isFreeDelivery"])) $binds[":isFreeDelivery"] = 0;

   		return array($query, $binds);
   	}
}
