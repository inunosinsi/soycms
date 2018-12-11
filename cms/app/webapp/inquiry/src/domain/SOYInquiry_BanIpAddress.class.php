<?php
/**
 * @table soyinquiry_ban_ip_address
 */
class SOYInquiry_BanIpAddress {

	/**
	 * @column ip_address
	 */
	private $ipAddress;

	/**
	 * @column log_date
	 */
	private $logDate;

	function getIpAddress(){
		return $this->ipAddress;
	}
	function setIpAddress($ipAddress){
		$this->ipAddress = $ipAddress;
	}

	function getLogDate(){
		return $this->logDate;
	}
	function setLogDate($logDate){
		$this->logDate = $logDate;
	}
}
