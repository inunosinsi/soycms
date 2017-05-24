<?php
/**
 * @entity shop.SOYShop_CategoryAttribute
 */
abstract class SOYShop_CategoryAttributeDAO extends SOY2DAO{

    abstract function insert(SOYShop_CategoryAttribute $bean);

    /**
     * @query #categoryId# = :categoryId AND #fieldId# = :fieldId
     */
    abstract function update(SOYShop_CategoryAttribute $bean);

    /**
     * @index fieldId
     */
    abstract function getByCategoryId($categoryId);

    /**
     * @return object
     * @query #categoryId# = :categoryId AND #fieldId# = :fieldId
     */
    abstract function get($categoryId,$fieldId);

    abstract function deleteByCategoryId($categoryId);

    /**
     * @query #categoryId# = :categoryId AND #fieldId# = :fieldId
     */
    abstract function delete($categoryId,$fieldId);

    abstract function deleteByFieldId($fieldId);
}
