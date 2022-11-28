<?php

class GenerateJancodeConfigPage extends WebPage{

	private $configObj;

	function __construct(){}

	function doPost(){
    }

	function execute(){
		parent::__construct();
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
