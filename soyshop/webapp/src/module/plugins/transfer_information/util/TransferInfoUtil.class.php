<?php

class TransferInfoUtil {

	const BANK_INFO = "transfer_bank_information";
	const PROP_BANK = "bank";		//金融機関名
	const PROP_BRANCH = "branch";	//支店名
	const PROP_DEPOSIT = "deposit";	//預金種別
	const PROP_NUMBER = "number";	//口座番号
	const PROP_HOLDER = "holder";	//口座名義

	private static function _depositTypes(){
		return array(
			"ordinary" => "普通預金",
			"current" => "当座預金"
		);
	}

	/**
	 * @return array
	 */
	public static function getDepositTypeList(){
		return self::_depositTypes();
	}

	/**
	 * @param string
	 * @return string
	 */
	public static function getDepositType(string $t){
		$list = self::_depositTypes();
		return (isset($list[$t])) ? $list[$t] : $list["ordinary"];
	}
}
