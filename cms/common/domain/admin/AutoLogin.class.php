<?php
/**
 * @table AutoLogin
 */
class AutoLogin {

	/**
	 * @column user_id
	 */
	private $userId;
	private $token;

	/**
	 * @column time_limit
	 */
	private $limit;



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
