<?php

class ItemListCategoryCustomfieldConfigFormPage extends WebPage{
	
	private $configObj;
	
	function ItemListCategoryCustomfieldConfigFormPage(){
		
	}
	
	function execute(){
		WebPage::WebPage();
	}
	
	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}