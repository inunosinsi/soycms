<?php

class RefundManagerForm extends WebPage{

	const NAMEPROP = "Customfield[refund_manager]";

	private $orderId;

	function __construct(){
		SOY2::import("module.plugins.refund_manager.util.RefundManagerUtil");
	}

	function execute(){
		parent::__construct();

		list($values, $isProcessed) = RefundManagerUtil::get($this->orderId);

		DisplayPlugin::toggle("is_processed", isset($values["type"]) && strlen($values["type"]));
		$this->addCheckBox("is_processed", array(
			"name" => "Customfield[refund_manager_processed]",
			"value" => 1,
			"selected" => ($isProcessed)
		));

		$this->addCheckBox("cancel", array(
			"name" => self::NAMEPROP . "[type]",
			"value" => RefundManagerUtil::TYPE_CANCEL,
			"selected" => (isset($values["type"]) && $values["type"] == RefundManagerUtil::TYPE_CANCEL),
			"elementId" => "cancel_man_type_" . RefundManagerUtil::TYPE_CANCEL
		));

		$this->addCheckBox("change", array(
			"name" => self::NAMEPROP . "[type]",
			"value" => RefundManagerUtil::TYPE_CHANGE,
			"selected" => (isset($values["type"]) && $values["type"] == RefundManagerUtil::TYPE_CHANGE),
			"elementId" => "cancel_man_type_" . RefundManagerUtil::TYPE_CHANGE
		));

		$this->addInput("refund", array(
			"name" => self::NAMEPROP . "[refund]",
			"value" => (isset($values["refund"])) ? $values["refund"] : "",
			"id" => "refund_manager_refund"
		));

		$this->addInput("increase", array(
			"name" => self::NAMEPROP . "[increase]",
			"value" => (isset($values["increase"])) ? $values["increase"] : "",
			"id" => "refund_manager_increase"
		));

		//銀行名
		$this->addInput("bank_name", array(
			"name" => self::NAMEPROP . "[bank_name]",
			"value" => (isset($values["bank_name"])) ? $values["bank_name"] : ""
		));

		//銀行口座　廃止
		$this->addInput("account", array(
			"name" => self::NAMEPROP . "[account]",
			"value" => (isset($values["account"])) ? $values["account"] : ""
		));

		$this->addInput("branch", array(
			"name" => self::NAMEPROP . "[branch]",
			"value" => (isset($values["branch"])) ? $values["branch"] : ""
		));

		//口座番号
		$this->addInput("account_number", array(
			"name" => self::NAMEPROP . "[account_number]",
			"value" => (isset($values["account_number"])) ? $values["account_number"] : ""
		));

		//口座種別　廃止
		$this->addInput("account_type", array(
			"name" => self::NAMEPROP . "[account_type]",
			"value" => (isset($values["account_type"])) ? $values["account_type"] : ""
		));

		$this->addInput("name", array(
			"name" => self::NAMEPROP . "[name]",
			"value" => (isset($values["name"])) ? $values["name"] : ""
		));
	}

	function setOrderId($orderId){
		$this->orderId = $orderId;
	}
}
