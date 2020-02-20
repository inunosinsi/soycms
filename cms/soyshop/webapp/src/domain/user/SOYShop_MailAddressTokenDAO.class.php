<?php
/**
 * @entity user.SOYShop_MailAddressToken
 */
abstract class SOYShop_MailAddressTokenDAO extends SOY2DAO{

    /**
	 * @return id
	 */
    abstract function insert(SOYShop_MailAddressToken $bean);

    abstract function update(SOYShop_MailAddressToken $bean);

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
	abstract function getByToken($token);

    abstract function delete($id);

	/**
	 * @final
	 */
	function deleteOldObjects(){
		try{
			$this->executeUpdateQuery("DELETE FROM soyshop_mail_address_token WHERE time_limit < " . time());
		}catch(Exception $e){
			//
		}

	}
}
