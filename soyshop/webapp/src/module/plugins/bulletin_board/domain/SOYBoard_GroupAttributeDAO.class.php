<?php
SOY2::import("module.plugins.bulletin_board.domain.SOYBoard_GroupAttribute");
/**
 * @entity SOYBoard_GroupAttribute
 */
abstract class SOYBoard_GroupAttributeDAO extends SOY2DAO{

    abstract function insert(SOYBoard_GroupAttribute $bean);

	/**
     * @query #groupId# = :groupId AND #fieldId# = :fieldId
     */
    abstract function update(SOYBoard_GroupAttribute $bean);

    /**
     * @index fieldId
     */
    abstract function getByGroupId($groupId);

	/**
	 * @index groupId
	 */
	abstract function getByFieldId($fieldId);

	/**
	 * @return object
	 * @query #groupId# = :groupId AND #fieldId# = :fieldId
	 */
    abstract function get($groupId,$fieldId);

    abstract function deleteByGroupId($groupId);

    /**
     * @query #groupId# = :groupId AND #fieldId# = :fieldId
     */
    abstract function delete($groupId,$fieldId);

    abstract function deleteByFieldId($fieldId);
}
