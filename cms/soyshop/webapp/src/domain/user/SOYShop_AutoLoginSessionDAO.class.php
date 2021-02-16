<?php
 /**
 * @entity user.SOYShop_AutoLoginSession
 */
abstract class SOYShop_AutoLoginSessionDAO extends SOY2DAO{

	abstract function insert(SOYShop_AutoLoginSession $obj);

	abstract function update(SOYShop_AutoLoginSession $obj);

	/**
	 * @query #limit# < :time
	 */
	abstract function deleteByTime($time);

	abstract function get();

	/**
	 * @return object
	 */
	abstract function getByToken($token);

	abstract function deleteByUserId($userId);

	/**
	 * @final
	 */
	function deleteOldObjects(){
		try{
			$this->executeUpdateQuery("DELETE FROM soyshop_auto_login WHERE time_limit < " . time());
		}catch(Exception $e){
			//
		}
	}
}
