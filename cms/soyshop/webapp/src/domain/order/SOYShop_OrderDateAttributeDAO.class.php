<?php

/**
 * @entity order.SOYShop_OrderDateAttribute
 */
abstract class SOYShop_OrderDateAttributeDAO extends SOY2DAO{

   	/**
	 * @return id
	 */
   	abstract function insert(SOYShop_OrderDateAttribute $bean);

   	/**
     * @query #orderId# = :orderId AND #fieldId# = :fieldId
     */
	abstract function update(SOYShop_OrderDateAttribute $bean);

	/**
	 * @return list
	 * @index fieldId
	 */
	abstract function getByOrderId($orderId);

	/**
	 * @return object
	 * @query #orderId# = :orderId AND #fieldId# = :fieldId
	 */
    abstract function get($orderId,$fieldId);

	/**
	 * @return object
	 * @query #orderId# = :orderId AND #fieldId# = :fieldId
	 * 後方互換
	 */
	abstract function getByOrderIdAndFieldId($orderId, $fieldId);

	/**
	 * @final
	 * @param int OrderId, array $fieldIds
	 * @return array
	 */
	function getByOrderIdAndFieldIds(int $orderId, array $fieldIds){
		if(!count($fieldIds)) return array();

		try{
			$res = $this->executeQuery(
				"SELECT * ".
				"FROM soyshop_order_date_attribute ".
				"WHERE order_id = :orderId ".
				"AND order_field_id IN (\"" . implode("\",\"", $fieldIds) . "\")",
				array(":orderId" => $orderId)
			);
		}catch(Exception $e){
			$res = array();
		}

		$list = array();
		if(count($res)){
			foreach($res as $v){
				$list[$v["order_field_id"]] = $this->getObject($v);
			}
		}

		foreach($fieldIds as $fieldId){
			if(!isset($list[$fieldId])){
				$attr = new SOYShop_OrderDateAttribute();
				$attr->setOrderId($orderId);
				$attr->setFieldId($fieldId);
				$list[$fieldId] = $attr;
			}
		}

		return $list;
	}

	/**
     * @query #orderId# = :orderId AND #fieldId# = :fieldId
     */
    abstract function delete($orderId, $fieldId);
}
