<?php

class FavoriteItemConfigFormPage extends WebPage{
	
	private $configObj;
	
	function FavoriteItemConfigFormPage(){
		
	}
	
	function execute(){
		WebPage::WebPage();
	}
	
	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
?>