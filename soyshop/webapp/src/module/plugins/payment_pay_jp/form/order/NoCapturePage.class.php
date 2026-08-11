<?php

class NoCapturePage extends WebPage{

	private $order;
	private $params;


	function __construct(){}

	function execute(){
		parent::__construct();

		$isCapture = (isset($this->params["capture"])) ? $this->params["capture"] : false;
		$expired = (isset($this->params["expired"]) && strlen($this->params["expired"])) ? date("Y/m/d H:i:s", $this->params["expired"]) : null;
		$refunded = (isset($this->params["refunded"])) ? $this->params["refunded"] : false;

		DisplayPlugin::toggle("capture", (!$isCapture && $this->params["expired"] > time() && !$refunded));
		DisplayPlugin::toggle("expired_over", (!$isCapture && $this->params["expired"] < time()));
		DisplayPlugin::toggle("refunded", $refunded);

		$this->addForm("form");

		$this->addLabel("expired", array(
			"text" => $expired
		));
	}

	function setOrder($order){
		$this->order = $order;
	}
	function setParams($params){
		$this->params = $params;
	}
}
