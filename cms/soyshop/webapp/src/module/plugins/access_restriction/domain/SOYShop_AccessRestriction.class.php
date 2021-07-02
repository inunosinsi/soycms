<?php
/**
 * @table soyshop_access_restriction
 */
class SOYShop_AccessRestriction {

	/**
	 * @column ip_address
	 */
	private $ipAddress;

	private $token;

	/**
	 * @column create_date
	 */
	private $createDate;

	function getIpAddress(){
		return $this->ipAddress;
	}
	function setIpAddress($ipAddress){
		$this->ipAddress = $ipAddress;
	}

	function getToken(){
		return $this->token;
	}
	function setToken($token){
		$this->token = $token;
	}

	function getCreateDate(){
		return $this->createDate;
	}
	function setCreateDate($createDate){
		$this->createDate = $createDate;
	}
}
