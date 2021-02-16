<?php
/**
 * @entity user.SOYShop_UserToken
 */
abstract class SOYShop_UserTokenDAO extends SOY2DAO{

    /**
	 * @return id
	 */
    abstract function insert(SOYShop_UserToken $bean);

	/**
	 * @column user_id = :userId
	 */
    abstract function update(SOYShop_UserToken $bean);

    abstract function get();

	/**
	 * @return object
	 */
	abstract function getByUserId($userId);

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
			$this->executeUpdateQuery("DELETE FROM soyshop_user_token WHERE time_limit < " . time());
		}catch(Exception $e){
			//
		}
	}
}
