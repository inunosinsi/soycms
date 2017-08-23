<?php
/**
 * @entity admin.Administrator
 * @date 2007-08-22 18:42:19
 */
abstract class AdministratorDAO extends SOY2DAO{
	
	/**
	 * @return id
	 */
	abstract function insert(Administrator $bean);

	abstract function update(Administrator $bean);

	abstract function delete($id);

	abstract function get();

	/**
	 * @return object
	 */
	abstract function getById($id);
	
	/**
	 * @return object
	 */
	abstract function getByUserId($userId);

	/**
	 * @return object
	 */
	abstract function getByEmail($email);
	
	/**
	 * @index id
	 * @column id,#userId#
	 */
	abstract function getNameMap();
	
	/**
	 * @return column_count
	 * @columns count(id) as count
	 * @query default_user = 1
	 */
	abstract function countDefaultUser();

	/**
	 * @return column_count
	 * @columns count(id) as count
	 */
	abstract function countUser();

	/**
	 * @return object
	 */
	abstract function getByToken($token);

	/**
	 * @return object
	 * @query #userId# = :userId AND #email# = :email
	 */
	abstract function getByUserIdAndEmail($userId, $email);
}
