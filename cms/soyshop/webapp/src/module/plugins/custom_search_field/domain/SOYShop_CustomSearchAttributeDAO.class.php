<?php
SOY2::import("module.plugins.custom_search_field.domain.SOYShop_CustomSearchAttribute");
/**
 * @entity SOYShop_CustomSearchAttribute
 */
abstract class SOYShop_CustomSearchAttributeDAO extends SOY2DAO{

    abstract function insert(SOYShop_CustomSearchAttribute $bean);

    /**
     * @query #searchId# = :searchId AND #fieldId# = :fieldId
     */
    abstract function update(SOYShop_CustomSearchAttribute $bean);

	/**
     * @index fieldId
     */
    abstract function getBySearchId($searchId);

    /**
     * @return object
     * @query #searchId# = :searchId AND #fieldId# = :fieldId
     */
    abstract function get($searchId,$fieldId);

    abstract function deleteBySearchId($searchId);

    /**
     * @query #searchId# = :searchId AND #fieldId# = :fieldId
     */
    abstract function delete($searchId,$fieldId);

    abstract function deleteByFieldId($fieldId);
}
