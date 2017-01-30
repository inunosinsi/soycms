<?php

class UpgradeLogic extends SOY2LogicBase{
	
	
	private $version;
	

	function upgrade(){

		include_once(dirname(__FILE__) . "/batch/upgrade" . $this->version.".php");
		execute();		
		
	}


	function getVersion() {
		return $this->version;
	}
	function setVersion($version) {
		$this->version = $version;
	}
}
?>