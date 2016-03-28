<?php
/**
 * @entity admin.AppRole
 */
abstract class AppRoleDAO extends SOY2DAO{
	
	abstract function insert(AppRole $bean);

	abstract function update(AppRole $bean);

	abstract function delete($id);
	
	/**
	 * @order #userId#,#appId#
	 */
	abstract function get();
	
	/**
	 * @return object
	 */
	abstract function getById($id);
	
	/**
	 * @return object
	 * @query ##appId## = :appId and ##userId## = :userId
	 */
	abstract function getRole($appId,$userId);

	/**
	 * @index appId
	 */
	abstract function getByUserId($userId);

	/**
	 * @index userId
	 */
	abstract function getByAppId($appId);
	
	abstract function deleteByUserId($userId);
	
	abstract function deleteByAppId($appId);
	
}
?>
