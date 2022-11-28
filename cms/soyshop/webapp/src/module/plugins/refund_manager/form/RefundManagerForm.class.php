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

		$this->addInput("bank_number", array(
			"name" => self::NAMEPROP . "[bank_number]",
			"value" => (isset($values["bank_number"])) ? $values["bank_number"] : null
		));

		//銀行名
		$name = (isset($values["bank_name"])) ? $values["bank_name"] : null;
		if(is_null($name)) $name = (isset($values["account"])) ? $values["account"] : "";
		$this->addInput("bank_name", array(
			"name" => self::NAMEPROP . "[bank_name]",
			"value" => $name
		));

		//銀行口座　廃止
		$this->addInput("account", array(
			"name" => self::NAMEPROP . "[account]",
			"value" => (isset($values["account"])) ? $values["account"] : ""
		));

		$this->addInput("branch_number", array(
			"name" => self::NAMEPROP . "[branch_number]",
			"value" => (isset($values["branch_number"])) ? $values["branch_number"] : ""
		));

		$this->addInput("branch", array(
			"name" => self::NAMEPROP . "[branch]",
			"value" => (isset($values["branch"])) ? $values["branch"] : ""
		));

		//口座種別は前はテキストフォームだったので、セレクトボックスの時のみ表示
		$this->addSelect("account_type", array(
			"name" => self::NAMEPROP . "[account_type]",
			"options" => RefundManagerUtil::getAccountTypeList(),
			"selected" => (isset($values["account_type"]) && strlen($values["account_type"]) === 1) ? $values["account_type"] : null
		));

		//口座番号 下の行は以前の修正の互換性維持のための対応
		$number = (isset($values["account_number"])) ? $values["account_number"] : null;
		if(is_null($number) && isset($values["account_type"]) && strlen($values["account_type"]) > 1) $numher = $values["account_type"];
		$this->addInput("account_number", array(
			"name" => self::NAMEPROP . "[account_number]",
			"value" => $number
		));

		//口座種別　廃止
		// $this->addInput("account_type", array(
		// 	"name" => self::NAMEPROP . "[account_type]",
		// 	"value" => (isset($values["account_type"])) ? $values["account_type"] : ""
		// ));

		$this->addInput("name", array(
			"name" => self::NAMEPROP . "[name]",
			"value" => (isset($values["name"])) ? $values["name"] : ""
		));

		$this->addTextArea("comment", array(
			"name" => self::NAMEPROP . "[comment]",
			"value" => (isset($values["comment"])) ? $values["comment"] : ""
		));
	}

	function setOrderId($orderId){
		$this->orderId = $orderId;
	}
}
