<?php
 /**
 * @entity admin.AutoLogin
 */
abstract class AutoLoginDAO extends SOY2DAO{

	/**
	 * @return id
	 */
	abstract function insert(AutoLogin $bean);

	abstract function update(AutoLogin $bean);

	abstract function delete($id);

	/**
	 * @return object
	 */
	abstract function getByToken($token);

	/**
	 * @query #limit# < :time
	 */
	abstract function deleteByTime($time);

	abstract function get();

	abstract function deleteByUserId($userId);

}
