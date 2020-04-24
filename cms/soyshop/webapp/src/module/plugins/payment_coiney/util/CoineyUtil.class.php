<?php

class CoineyUtil {

	private static function _errorMessageList(){
		return array(
			"no_api_key" => "Authorizationヘッダがない",
			"invalid_api_key" => "API Keyが失効しているか、その他の理由で不正",
			"already_processed" => "処理済みの支払いを操作した",
			"invalid_parameter" => "パラメータに関するバリデーションエラー",
			"merchant_not_allowed" => "マーチャントに許可されていない操作をした",
			"payment_not_found" => "存在しない支払いが指定された"
		);
	}

	public static function getErrorMessageList(){
		return self::_errorMessageList();
	}

	public static function getErrorText($code){
		$errorCodes = self::_errorMessageList();

		if(!isset($errorCodes[$code])) $code = "invalid_api_key";
		return $errorCodes[$code];
	}

	private static function _statusList(){
		return array(
			"open" => "未払い",
			"expired" => "期限切れ",
			"paid" => "支払済み",
			"refunded" => "売上取消・返品済み",
			"cancelled" => "無効"
		);
	}

	public static function getStatusText($code){
		$statusCodes = self::_statusList();

		if(!isset($statusCodes[$code])) $code = "open";
		return $statusCodes[$code];
	}

	public static function getConfig(){
		return self::_getConfig();
	}

	private static function _getConfig(){
		return SOYShop_DataSets::get("payment_coiney.config", array(
			"secret_key" => ""
		));
	}

	public static function saveConfig($values){
		SOYShop_DataSets::put("payment_coiney.config", $values);
	}
}
