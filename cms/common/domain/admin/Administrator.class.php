<?php
/**
 * @table Administrator
 * @date 2007-08-22 18:42:19
 */
class Administrator {

	/**
	 * @id identity
	 */
	private $id;

	/**
	 * @column user_id
	 */
	private $userId;

	/**
	 * @column user_password
	 */
	private $userPassword;

	/**
	 * @column default_user
	 */
	private $isDefaultUser;

	private $email;

	private $name;

	/**
	 * @column token
	 */
	private $token;

	/**
	 * @column token_issued_date
	 */
	private $tokenIssuedDate;

	public function setId($id){
		$this->id = $id;
	}

	public function getId(){
		return $this->id;
	}

	public function setUserId($userId){
		$this->userId = $userId;
	}

	public function getUserId(){
		return $this->userId;
	}

	public function setUserPassword($userPassword){
		$this->userPassword = $userPassword;
	}

	public function getUserPassword(){
		return $this->userPassword;
	}

	function getIsDefaultUser() {
		return $this->isDefaultUser;
	}
	function setIsDefaultUser($isDefaultUser) {
		$this->isDefaultUser = (int)$isDefaultUser;
	}

	function getEmail() {
		return $this->email;
	}
	function setEmail($email) {
		$this->email = $email;
	}
	function getName() {
		return $this->name;
	}
	function setName($name) {
		$this->name = $name;
	}

	function getToken() {
		return $this->token;
	}
	function setToken($token) {
		$this->token = $token;
	}

	function getTokenIssuedDate() {
		return $this->tokenIssuedDate;
	}
	function setTokenIssuedDate($tokenIssuedDate) {
		$this->tokenIssuedDate = $tokenIssuedDate;
	}
}
