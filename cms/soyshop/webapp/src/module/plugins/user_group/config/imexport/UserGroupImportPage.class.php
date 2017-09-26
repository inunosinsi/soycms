<?php

class UserGroupImportPage extends WebPage{

	private $configObj;

	function __construct(){
	}

	function execute(){
		parent::__construct();
	}

	function setConfigObj($configObj){
        $this->configObj = $configObj;
	}
}
