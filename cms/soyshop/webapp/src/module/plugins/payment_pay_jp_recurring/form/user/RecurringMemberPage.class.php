<?php

class RecurringMemberPage extends WebPage{

	private $user;

	function __construct(){}

	function execute(){
		parent::__construct();

		$logic = SOY2Logic::createInstance("module.plugins.payment_pay_jp_recurring.logic.RecurringLogic");

		//顧客IDから定期課金のIDを取得する
		$subscribeId = $logic->getSubscribeIdByUserId($this->user->getId());
		
		//PAY.JPの顧客IDを取得
		$customId = $logic->getCustomerTokenByUserId($this->user->getId());
		// $token = SOY2Logic::createInstance("module.plugins.payment_pay_jp.logic.PayJpLogic")->getCustomerTokenByUserId($this->user->getId());
		//
		// DisplayPlugin::toggle("register_token", isset($token));
		// DisplayPlugin::toggle("no_register_token", !isset($token));
		//
		// $this->addLabel("token", array(
		// 	"text" => $token
		// ));
	}

	function setUser($user){
		$this->user = $user;
	}
}
