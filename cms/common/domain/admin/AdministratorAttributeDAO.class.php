<?php
/**
 * @entity admin.AdministratorAttribute
 */
abstract class AdministratorAttributeDAO extends SOY2DAO{

	abstract function insert(AdministratorAttribute $bean);

	/**
     * @query #adminId# = :adminId AND #fieldId# = :fieldId
     */
    abstract function update(AdministratorAttribute $bean);

    /**
     * @index fieldId
     */
    abstract function getByAdminId($adminId);

	/**
	 * @return object
	 * @query #adminId# = :adminId AND #fieldId# = :fieldId
	 */
    abstract function get($adminId, $fieldId);

    abstract function deleteByAdminId($adminId);

    /**
     * @query #adminId# = :adminId AND #fieldId# = :fieldId
     */
    abstract function delete($adminId, $fieldId);

    abstract function deleteByFieldId($fieldId);
}
