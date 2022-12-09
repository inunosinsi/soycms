<?php
/**
 * @entity order.SOYShop_OrderAttribute
 */
abstract class SOYShop_OrderAttributeDAO extends SOY2DAO{

   	/**
	 * @return id
	 */
   	abstract function insert(SOYShop_OrderAttribute $bean);

   	/**
     * @query #orderId# = :orderId AND #fieldId# = :fieldId
     */
	abstract function update(SOYShop_OrderAttribute $bean);

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
