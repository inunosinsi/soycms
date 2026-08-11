<?php

class AmazonPayOperatePage extends WebPage{

	private $order;

	function __construct(){}

	function execute(){
		parent::__construct();

		$this->addForm("form");
	}

	function setOrder($order){
		$this->order = $order;
	}
}
