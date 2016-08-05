<?php

class FavoriteItemConfigFormPage extends WebPage{
	
	private $configObj;
	
	function __construct(){}
	
	function execute(){
		WebPage::WebPage();
	}
	
	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
?>