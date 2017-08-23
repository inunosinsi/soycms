<?php
/**
 * @entity admin.SiteRole
 * @date 2007-08-22 18:42:19
 */
abstract class SiteRoleDAO extends SOY2DAO{

	abstract function insert(SiteRole $bean);

	abstract function update(SiteRole $bean);

	abstract function delete($id);
	
	/**
	 * @order #userId#,#siteId#
	 */
	abstract function get();
	
	/**
	 * @return object
	 */
	abstract function getById($id);
	
	/**
	 * @return object
	 * @query ##siteId## = :siteId and ##userId## = :userId
	 */
	abstract function getSiteRole($siteId,$userId);

	abstract function getByUserId($userId);

	abstract function getBySiteId($siteId);
	
	abstract function deleteByUserId($userId);
	
	abstract function deleteBySiteId($siteId);
	
	/**
	 * @query ##siteId## = :siteId and ##userId## = :userId
	 */
	abstract function deleteSiteRole($userId,$siteId);
}
?>
