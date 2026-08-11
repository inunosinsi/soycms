<?php

class CoineyApiLogic extends SOY2LogicBase {

	function __construct(){
		SOY2::import("module.plugins.payment_coiney.util.CoineyUtil");
	}

	function createPaymentRequest(int $orderId){
		static $json;
		if(is_null($json)){
			$order = soyshop_get_order_object($orderId);
			$config = CoineyUtil::getConfig();

			$headers = array(
				"Authorization: Bearer " . $config["key"],
				"X-CoineyPayge-Version: 2016-10-25",
				"Accept: application/json",
				"Content-Type: application/json"
			);

			$data = array(
				"amount" => (int)$order->getPrice(),
				"currency" => "jpy",
				"redirectUrl" => soyshop_get_cart_url(false, true) . "?complete=1",
				"cancelUrl" => soyshop_get_cart_url(false, true) . "?cancel=1",
				"method" => "creditcard",
				"description" => "payment via soyshop's cart",
				"metadata" => array("orderId" => $order->getId())
			);

			$ch = curl_init("https://api.coiney.io/api/v1/payments");
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$result = curl_exec($ch);
			curl_close($ch);

			$json = json_decode($result, true);
		}

		return $json;
	}

	//支払いIDから支払い情報を取得
	function getPaymentInfoById($paymentId){
		static $json;
		if(is_null($json)){
			$config = CoineyUtil::getConfig();

			$headers = array(
				"Authorization: Bearer " . $config["key"],
				"X-CoineyPayge-Version: 2016-10-25",
				"Accept: application/json",
				"Content-Type: application/json"
			);

			$ch = curl_init("https://api.coiney.io/api/v1/payments/" . $paymentId);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_POST, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$result = curl_exec($ch);
			curl_close($ch);

			$json = json_decode($result, true);
		}

		return $json;
	}
}
