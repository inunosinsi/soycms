<?php
/**
 * @table soylpo_config
 */
class SOYLpo_Config {

	/**
	 * @id
	 */
	private $id;
	private $wisywig;
	
	function getId(){
		return $this->id;
	}
	function setId($id){
		$this->id = $id;
	}
	
	function getWisywig(){
		return $this->wisywig;
	}
	function setWisywig($wisywig){
		$this->wisywig = $wisywig;
	}
}
?>