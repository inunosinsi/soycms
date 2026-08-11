<?php

class CreditMemberPage extends WebPage{

	private $user;

	function __construct(){}

	function execute(){
		parent::__construct();

		$token = SOY2Logic::createInstance("module.plugins.payment_pay_jp.logic.PayJpLogic")->getCustomerTokenByUserId($this->user->getId());

		DisplayPlugin::toggle("register_token", isset($token));
		DisplayPlugin::toggle("no_register_token", !isset($token));

		$this->addLabel("token", array(
			"text" => $token
		));
	}

	function setUser($user){
		$this->user = $user;
	}
}
