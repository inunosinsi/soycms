<?php

class AmazonPayCardSelectPage extends WebPage{

	function __construct(){
		SOY2::import("module.plugins.payment_amazon_pay.util.AmazonPayUtil");
	}

	function doPost(){}

	function execute(){
		//念の為にアクセストークンを取得しておく
		$_SESSION["access_token"] = $_GET["access_token"];

		parent::__construct();

		$cnf = AmazonPayUtil::getConfig(false);

		$this->addForm("form");

		$this->addLabel("merchant_id", array(
			"text" => $cnf["merchant_id"]
		));

		$this->addLabel("client_id", array(
			"text" => $cnf["client_id"]
		));

		$this->addLabel("action_url", array(
			"text" => AmazonPayUtil::getActionUrl()
		));

		$this->addLink("back_link", array(
			"link" => AmazonPayUtil::getBackUrl()
		));
	}
}
