<?php
 /**
 * @entity user.SOYShop_AutoLoginSession
 */
abstract class SOYShop_AutoLoginSessionDAO extends SOY2DAO{
	
	/**
	 * @return id
	 */
	abstract function insert(SOYShop_AutoLoginSession $obj);
	
	abstract function update(SOYShop_AutoLoginSession $obj);
	
	abstract function delete($id);
	
	/**
	 * @query #limit# < :time
	 */
	abstract function deleteByTime($time);
	
	abstract function get();
	
	/**
	 * @return object
	 */
	abstract function getById($id);
	
	/**
	 * @return object
	 */
	abstract function getByToken($token);
	
	abstract function deleteByUserId($userId);
	
}
?>
