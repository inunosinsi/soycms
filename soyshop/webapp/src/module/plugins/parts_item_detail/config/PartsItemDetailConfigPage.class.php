<?php

class PartsItemDetailConfigPage extends WebPage{

	private $config;

	function __construct(){}

	function execute(){
		parent::__construct();
	}

	function setConfigObj($obj) {
		$this->config = $obj;
	}
}
