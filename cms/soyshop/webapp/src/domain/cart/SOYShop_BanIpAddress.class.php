<?php
/**
 * @table soyshop_ban_ip_address
 */
class SOYShop_banIpAddress {

	/**
	 * @column ip_address
	 */
	private $ipAddress;

	/**
	 * @column plugin_id
	 */
	private $pluginId;

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

	function getPluginId(){
		return $this->pluginId;
	}
	function setPluginId($pluginId){
		$this->pluginId = $pluginId;
	}

	function getLogDate(){
		return $this->logDate;
	}
	function setLogDate($logDate){
		$this->logDate = $logDate;
	}
}
