<?php

class AsyncCartButtonConfigPage extends WebPage{
	
	private $configObj;
	
	function AsyncCartButtonConfigPage(){}
	
	function execute(){
		WebPage::WebPage();
		
		$this->addImage("img_cart", array(
			"src" => "/" . SOYSHOP_ID . "/themes/sample/soyshop_async_add_item.png"
		));
	}
	
	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
?>