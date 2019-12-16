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
     * @query #orderId# = :orderId AND #fieldId# = :fieldId
     */
    abstract function delete($orderId, $fieldId);
}
