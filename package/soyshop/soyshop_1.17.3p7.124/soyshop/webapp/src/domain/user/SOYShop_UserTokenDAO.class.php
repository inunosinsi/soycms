<?php
/**
 * @entity user.SOYShop_UserToken
 */
abstract class SOYShop_UserTokenDAO extends SOY2DAO{

    /**
	 * @return id
	 */
    abstract function insert(SOYShop_UserToken $bean);

    abstract function update(SOYShop_UserToken $bean);

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


}
?>
