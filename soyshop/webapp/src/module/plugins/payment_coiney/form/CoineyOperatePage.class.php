<?php

class CoineyOperatePage extends WebPage {

	private $order;
	private $info;

	function __construct(){
		SOY2::import("module.plugins.payment_coiney.util.CoineyUtil");
	}

	function execute(){
		parent::__construct();

		$this->addForm("form");

		$this->addLabel("status", array(
			"text" => (isset($this->info["status"])) ? CoineyUtil::getStatusText($this->info["status"]) : ""
		));
	}

	function setOrder($order){
		$this->order = $order;
	}
	function setInfo($info){
		$this->info = $info;
	}
}
