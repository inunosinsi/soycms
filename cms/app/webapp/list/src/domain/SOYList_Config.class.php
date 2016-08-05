<?php

/**
 * @table soylist_config
 */

class SOYList_Config {

	private $config;
	
	function getConfig(){
		return $this->config;
	}
	function setConfig($config){
		$this->config = $config;
	}
	
	/** 便利メソッド **/
	
	function getConfigArray(){
		$array = soy2_unserialize($this->config);
		if(!is_array($array)){
			$array = array();
		}
		return $array;
	}
	
}
?>