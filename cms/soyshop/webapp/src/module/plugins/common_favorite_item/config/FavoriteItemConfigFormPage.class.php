<?php

class FavoriteItemConfigFormPage extends WebPage{
	
	private $configObj;
	
	function __construct(){}
	
	function execute(){
		WebPage::__construct();
	}
	
	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
?>