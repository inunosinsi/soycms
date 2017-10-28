<?php

class RecurringMemberPage extends WebPage{

	private $user;

	function __construct(){}

	function execute(){
		parent::__construct();

		$logic = SOY2Logic::createInstance("module.plugins.payment_pay_jp_recurring.logic.RecurringLogic");
		$logic->initPayJp();

		//顧客IDから定期課金のIDを取得する
		list($subscribeId, $orderId) = $logic->getSubscribeIdAndOrderIdByUserId($this->user->getId());

		DisplayPlugin::toggle("subscribe", isset($subscribeId));
		DisplayPlugin::toggle("no_subscribe", !isset($subscribeId));

		$this->addForm("form");

		$this->addLabel("subscribe_token", array(
			"text" => $subscribeId
		));

		//PAY.JPの顧客IDを取得
		$customerId = $logic->getCustomerTokenByUserId($this->user->getId());
		$this->addLabel("customer_token", array(
			"text" => $customerId
		));

		//プランの取得
		$planId = null;
		$planName = null;
		if(isset($subscribeId)){
			list($res, $err) = $logic->retrievePlan($subscribeId);
			if(isset($res)){
				$plan = $res->plan;
				$planId = $plan->id;
				$interval = ($plan->interval == "month") ? "月" : "?";
				$planName = $plan->name . "(¥" . $plan->amount ."/" . $interval .")";
			}
		}

		$this->addLabel("plan_name", array(
			"text" => $planName
		));

		$this->addInput("subscribe_token_hidden", array(
			"name" => "Subscribe",
			"value" => $subscribeId
		));

		$this->addInput("order_id_hidden", array(
			"name" => "OrderId",
			"value" => $orderId
		));

		//プラン一覧
		$planList = $logic->getPlanList();

		$this->addSelect("plan", array(
			"name" => "Plan",
			"options" => $planList,
			//"selected" => $planId
		));
	}

	function setUser($user){
		$this->user = $user;
	}
}
