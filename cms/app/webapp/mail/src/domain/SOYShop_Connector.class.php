<?php
/**
 * @table soymail_soyshop_connector
 */
class SOYShop_Connector {
	
	/**
	 * @id
	 */
	private $id;
	
	/**
	 * @column shop_db_path
	 */
	private $shopDbPath;
	
	/**
	 * @column user_id
	 */
	private $userId;
	
	/**
	 * @column password
	 */
	private $password;
	
	/**
	 * @column is_connect
	 */
	private $isConnect;
	
	
	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}
	function getShopDbPath() {
		return $this->shopDbPath;
	}
	function setShopDbPath($shopDbPath) {
		$this->shopDbPath = $shopDbPath;
	}
	function getUserId() {
		return $this->userId;
	}
	function setUserId($userId) {
		$this->userId = $userId;
	}
	function getPassword() {
		return $this->password;
	}
	function setPassword($password) {
		$this->password = $password;
	}
	function getIsConnect() {
		return $this->isConnect;
	}
	function setIsConnect($isConnect) {
		$this->isConnect = $isConnect;
	}

}
?>