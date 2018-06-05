<?php
SOY2::import("module.plugins.slip_number.domain.SOYShop_SlipNumber");

/**
 * @entity SOYShop_SlipNumber
 */
abstract class SOYShop_SlipNumberDAO extends SOY2DAO {

	/**
	 * @return id
	 * @trigger onInsert
	 */
	abstract function insert(SOYShop_SlipNumber $bean);

	/**
	 * @trigger onUpdate
	 */
	abstract function update(SOYShop_SlipNumber $bean);

	/**
	 * @return object
	 */
	abstract function getById($id);

	abstract function getByOrderId($orderId);

	abstract function getByIsDelivery($isDelivery);

	/**
	 * @return object
	 */
	abstract function getBySlipNumber($slipNumber);

	/**
	 * @return object
	 * @query slip_number = :slipNumber AND is_delivery = 0
	 */
	abstract function getBySlipNumberAndNoDelivery($slipNumber);

	abstract function deleteById($id);

	/**
	 * @query slip_number = :slipNumber
	 */
	abstract function deleteBySlipNumber($slipNumber);

	/**
	 * @query slip_number = :slipNumber AND order_id = :orderId
	 */
	abstract function deleteBySlipNumberWithOrderId($slipNumber, $orderId);

	/**
	 * @final
	 */
	function getRegisteredNumberListByOrderId($orderId){
		$sql = "SELECT id, slip_number FROM soyshop_slip_number ".
				"WHERE order_id = :orderId";
		try{
			$results = $this->executeQuery($sql, array(":orderId" => $orderId));
		}catch(Exception $e){
			return array();
		}

		if(!count($results)) return array();

		$list = array();
		foreach($results as $res){
			$list[$res["slip_number"]] = 0;
		}
		return $list;
	}

	/**
	 * @final
	 */
	function countNoDeliveryByOrderId($orderId){
		$sql = "SELECT COUNT(id) AS TOTAL FROM soyshop_slip_number WHERE order_id = :orderId AND is_delivery = 0";
		try{
			$res = $this->executeQuery($sql, array(":orderId" => $orderId));
			return (isset($res[0]["TOTAL"])) ? (int)$res[0]["TOTAL"] : 0;
		}catch(Exception $e){
			return 0;
		}
	}

	/**
	 * @final
	 */
	function onInsert($query, $binds){
		if(!isset($binds[":isDelivery"])) $binds[":isDelivery"] = 0;
		$binds[":createDate"] = time();
		$binds[":updateDate"] = time();
		return array($query, $binds);
	}

	/**
	 * @final
	 */
	function onUpdate($query, $binds){
		$binds[":updateDate"] = time();
		return array($query, $binds);
	}
}
