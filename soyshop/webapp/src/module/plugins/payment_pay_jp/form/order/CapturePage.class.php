<?php

class CapturePage extends WebPage{

	private $order;
	private $params;


	function __construct(){}

	function execute(){
		parent::__construct();

		$isCapture = (isset($this->params["capture"])) ? $this->params["capture"] : false;
		$capturedAt = (isset($this->params["captured_at"]) && strlen($this->params["captured_at"])) ? date("Y/m/d H:i:s", $this->params["captured_at"]) : null;
		$refunded = (isset($this->params["refunded"])) ? $this->params["refunded"] : false;

		DisplayPlugin::toggle("no_refunded", !$refunded);
		DisplayPlugin::toggle("refunded", $refunded);

		$this->addForm("form");

		$this->addLabel("captured_at", array(
			"text" => $capturedAt
		));
	}

	function setOrder($order){
		$this->order = $order;
	}
	function setParams($params){
		$this->params = $params;
	}
}
