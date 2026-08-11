<?php
class ShoppingMallConfigPage extends WebPage{

	private $configObj;

	function __construct() {}

	function doPost(){}

	function execute(){
		parent::__construct();
	}

	function setConfigObj(ShoppingMallConfig $configObj) {
		$this->configObj = $configObj;
	}
}
