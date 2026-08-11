<?php

class ReceiptUtil {

	public static function getConfig(){
		return self::_config();
	}

	public static function isMyPageSetting(){
		$cnf = self::_config();
		return (isset($cnf["mypage"]) && (int)$cnf["mypage"] === 1);
	}
	
	private static function _config(){
		return SOYShop_DataSets::get("order_invoice_add_receipt_button.config", array(
			"mypage" => 0
		));
	}

	public static function saveConfig(array $values){
		return SOYShop_DataSets::put("order_invoice_add_receipt_button.config", $values);
	}
}