<?php

class ProsperityReportConfigPage extends WebPage{
	
	private $configObj;
	
	function __construct(){}
	
	function doPost(){
		
	}
	
	function execute(){
		WebPage::__construct();
	}
	
	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}