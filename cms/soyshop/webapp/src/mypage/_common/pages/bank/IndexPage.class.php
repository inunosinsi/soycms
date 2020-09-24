<?php

class IndexPage extends MainMyPagePageBase{

	function doPost(){
		if(soy2_check_token() && soy2_check_referer()){
			$mypage = $this->getMyPage();
			$mypage->setAttribute(TransferInfoUtil::BANK_INFO, $_POST[TransferInfoUtil::BANK_INFO]);
			$mypage->save();

			$this->jump("bank/confirm");
		}
	}

	function __construct(){
		$this->checkIsLoggedIn(); //ログインチェック

		SOY2::import("util.SOYShopPluginUtil");
		if(!SOYShopPluginUtil::checkIsActive("transfer_information")){
			$this->jump("top");
		}

		SOY2::import("module.plugins.transfer_information.util.TransferInfoUtil");

		parent::__construct();

		self::buildForm();
	}

	function buildForm(){
		$this->addForm("form");

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

		$this->addLabel("deposit_type_text", array(
			"text" => TransferInfoUtil::getDepositType(self::_value(TransferInfoUtil::PROP_DEPOSIT))
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
			$v = $this->getMypage()->getAttribute(TransferInfoUtil::BANK_INFO);
			if(is_null($v)){
				$attr = TransferInfoUtil::getUserAttr($this->getUser()->getId(), TransferInfoUtil::BANK_INFO);
				$v = (strlen($attr->getValue())) ? soy2_unserialize($attr->getValue()) : array();
			}
		}
		return (isset($v[$t])) ? $v[$t] : "";
	}
}
