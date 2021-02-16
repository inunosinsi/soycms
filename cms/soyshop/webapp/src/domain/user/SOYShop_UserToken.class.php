<?php
/**
 * @table soyshop_user_token
 */
class SOYShop_UserToken extends SOY2DAO_EntityBase{

	/**
	 * @column user_id
	 */
	private $userId;

	private $token;

	/**
	 * @column time_limit
	 */
	private $limit;


	function check(){
		return true;
	}

	function getUserId() {
		return $this->userId;
	}
	function setUserId($userId) {
		$this->userId = $userId;
	}

	function getToken() {
		return $this->token;
	}
	function setToken($token) {
		$this->token = $token;
	}

	function getLimit() {
		return $this->limit;
	}
	function setLimit($limit) {
		$this->limit = $limit;
	}
}
