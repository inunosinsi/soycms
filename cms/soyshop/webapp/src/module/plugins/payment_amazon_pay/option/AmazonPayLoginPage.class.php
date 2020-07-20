<?php

class AmazonPayLoginPage extends WebPage{

	function __construct(){
		SOY2::import("module.plugins.payment_amazon_pay.util.AmazonPayUtil");
	}

	function doPost(){}

	function execute(){
		parent::__construct();

		$cnf = AmazonPayUtil::getConfig(false);

		$this->addLabel("merchant_id", array(
			"text" => $cnf["merchant_id"]
		));

		$this->addLabel("client_id", array(
			"text" => $cnf["client_id"]
		));

		$this->addLabel("redirect_url", array(
			"text" => AmazonPayUtil::getRedirectUrl()
		));

		$this->addLink("back_link", array(
			"link" => AmazonPayUtil::getBackUrl()
		));
	}
}
