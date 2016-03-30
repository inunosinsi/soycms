<?php
/**
 * @entity shop.SOYShop_ItemAttribute
 */
abstract class SOYShop_ItemAttributeDAO extends SOY2DAO{

    abstract function insert(SOYShop_ItemAttribute $bean);

	/**
     * @query #itemId# = :itemId AND #fieldId# = :fieldId
     */
    abstract function update(SOYShop_ItemAttribute $bean);

    /**
     * @index fieldId
     */
    abstract function getByItemId($itemId);

	/**
	 * @return object
	 * @query #itemId# = :itemId AND #fieldId# = :fieldId
	 */
    abstract function get($itemId,$fieldId);

    abstract function deleteByItemId($itemId);

    /**
     * @query #itemId# = :itemId AND #fieldId# = :fieldId
     */
    abstract function delete($itemId,$fieldId);

    abstract function deleteByFieldId($fieldId);
}
?>