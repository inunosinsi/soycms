<?php
SOY2::import("module.plugins.returns_slip_number.domain.SOYShop_ReturnsSlipNumber");

/**
 * @entity SOYShop_ReturnsSlipNumber
 */
abstract class SOYShop_ReturnsSlipNumberDAO extends SOY2DAO {

	/**
	 * @return id
	 * @trigger onInsert
	 */
	abstract function insert(SOYShop_ReturnsSlipNumber $bean);

	/**
	 * @trigger onUpdate
	 */
	abstract function update(SOYShop_ReturnsSlipNumber $bean);

	/**
	 * @return object
	 */
	abstract function getById($id);

	abstract function getByOrderId($orderId);

	abstract function getByIsReturn($isReturn);

	abstract function deleteById($id);

	abstract function deleteBySlipNumber($slipNumber);

	/**
	 * @final
	 */
	function getRegisteredNumberListByOrderId($orderId){
		$sql = "SELECT id, slip_number FROM soyshop_returns_slip_number ".
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
	function countNoReturnByOrderId($orderId){
		$sql = "SELECT COUNT(id) AS TOTAL FROM soyshop_returns_slip_number WHERE order_id = :orderId AND is_return = 0";
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
		if(!isset($binds[":isReturn"])) $binds[":isReturn"] = 0;
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
