<?php
/**
 * @entity user.SOYShop_UserAttribute
 */
abstract class SOYShop_UserAttributeDAO extends SOY2DAO{

    abstract function insert(SOYShop_UserAttribute $bean);

	/**
     * @query #userId# = :userId AND #fieldId# = :fieldId
     */
    abstract function update(SOYShop_UserAttribute $bean);

    /**
     * @index fieldId
     */
    abstract function getByUserId($userId);

	/**
	 * @return object
	 * @query #userId# = :userId AND #fieldId# = :fieldId
	 */
    abstract function get($userId,$fieldId);

	/**
	 * @return object
	 */
	abstract function getAll();

    abstract function deleteByUserId($userId);

    /**
     * @query #userId# = :userId AND #fieldId# = :fieldId
     */
    abstract function delete($userId,$fieldId);

    abstract function deleteByFieldId($fieldId);
}
?>
