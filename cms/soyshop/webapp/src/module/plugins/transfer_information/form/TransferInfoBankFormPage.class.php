<?php

class TransferInfoBankFormPage extends WebPage {

	private $userId;

	function __construct(){
		SOY2::import("module.plugins.transfer_information.util.TransferInfoUtil");
	}

	function execute(){
		parent::__construct();

		$this->addInput("bank_name", array(
			"name" => self::_nameprop(TransferInfoUtil::PROP_BANK),
			"value" => self::_value(TransferInfoUtil::PROP_BANK),
			"attr:placeholder" => "○○銀行"
		));

		$this->addInput("branch_name", array(
			"name" => self::_nameprop(TransferInfoUtil::PROP_BRANCH),
			"value" => self::_value(TransferInfoUtil::PROP_BRANCH),
			"attr:placeholder" => "△△支店"
		));

		$this->addSelect("deposit_type", array(
			"name" => self::_nameprop(TransferInfoUtil::PROP_DEPOSIT),
			"options" => TransferInfoUtil::getDepositTypeList(),
			"selected" => self::_value(TransferInfoUtil::PROP_DEPOSIT)
		));

		$this->addInput("account_number", array(
			"name" => self::_nameprop(TransferInfoUtil::PROP_NUMBER),
			"value" => self::_value(TransferInfoUtil::PROP_NUMBER),
			"attr:pattern" => "^[0-9]+$"
		));

		$this->addInput("account_holder", array(
			"name" => self::_nameprop(TransferInfoUtil::PROP_HOLDER),
			"value" => self::_value(TransferInfoUtil::PROP_HOLDER)
		));
	}

	private function _nameprop($t){
		return TransferInfoUtil::BANK_INFO . "[" . $t . "]";
	}

	private function _value($t){
		static $v;
		if(is_null($v)){
			$attr = TransferInfoUtil::getUserAttr($this->userId, TransferInfoUtil::BANK_INFO);
			$v = (strlen($attr->getValue())) ? soy2_unserialize($attr->getValue()) : array();
		}
		return (isset($v[$t])) ? $v[$t] : "";
	}

	function setUserId($userId){
		$this->userId = $userId;
	}
}
