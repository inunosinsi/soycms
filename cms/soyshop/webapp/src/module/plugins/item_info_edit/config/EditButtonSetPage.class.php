<?php

class EditButtonSetPage extends WebPage{

	private $configObj;

	function __construct(){}

	function execute(){
		if(method_exists("WebPage", "WebPage")){
			WebPage::WebPage();
		}else{
			parent::__construct();
		}
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
