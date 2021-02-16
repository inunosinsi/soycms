<?php
/**
 * @table soyshop_mail_address_token
 */
class SOYShop_MailAddressToken extends SOY2DAO_EntityBase{

	/**
	 * @column user_id
	 */
	private $userId;

	/**
	 * @column new_mail_address
	 */
	private $new;

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

	function getNew(){
		return $this->new;
	}
	function setNew($new){
		$this->new = $new;
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
