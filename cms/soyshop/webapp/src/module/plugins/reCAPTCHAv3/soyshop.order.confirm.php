<?php
class reCAPTCHAv3OrderConfirm extends SOYShopOrderConfirmBase{

	/**
	 * @param string
	 * @return boolean エラーがあった場合はtrueを返す
	 */
	function checkError(string $param){
		SOY2::import("module.plugins.reCAPTCHAv3.util.reCAPTCHAUtil");
		$cnf = reCAPTCHAUtil::getConfig();
		if(!isset($cnf["secret_key"])) return false;

		$ch = curl_init("https://www.google.com/recaptcha/api/siteverify");
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array("secret" => $cnf["secret_key"], "response" => $param)));
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, TRUE);
		$out = curl_exec($ch);
		
		$json = json_decode($out);
		
		//@ToDo scoreを見て挙動を確認する スコアは0.0〜1.0で0.5が人とボットの閾値
		if(!$json->success || $json->score < 0.5){
			return true;
		}
		
		return false;
	}
	
	/**
	 * エラーメッセージ表示用のメソッド
	 * @param bool
	 * @return string
	 */
	function error(bool $isErr){
		$cart = CartLogic::getCart();
		$err = $cart->getErrorMessage("order_confirm_error");
		return (is_string($err) && strlen($err) > 0) ? "現在のアクセスはスパム判定されています。" : "";
	}
}
SOYShopPlugin::extension("soyshop.order.confirm", "reCAPTCHAv3", "reCAPTCHAv3OrderConfirm");
