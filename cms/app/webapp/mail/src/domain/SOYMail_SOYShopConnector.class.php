<?php

/**
 * @table soymail_soyshop_connector
 */

class SOYMail_SOYShopConnector {

	private $config;

	function getConfig(){
		return $this->config;
	}
	function setConfig($config){
		$this->config = $config;
	}

	/** 便利メソッド **/

	function getConfigArray(){
		return soy2_unserialize((string)$this->config);
	}
}
