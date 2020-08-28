<?php
require dirname(dirname(__FILE__)) . '/lib/AmazonPay/Client.php';

Use AmazonPay\Client;

class AmazonPayLogic extends SOY2LogicBase {

	function __construct(){
		SOY2::import("module.plugins.payment_amazon_pay.util.AmazonPayUtil");
	}

	function pay(SOYShop_Order $order){
		$referenceId = $_REQUEST['orderReferenceId'];
		$client = self::_client();

		// (2) 注文情報をセット
		$client->SetOrderReferenceDetails(array(
			'merchant_id' => AMAZON_PAY_MERCHANT_ID,
			'amazon_order_reference_id' => $referenceId,
			'amount' => $order->getPrice(),
			'currency_code' => 'JPY',
			'seller_note' => 'ご購入ありがとうございます',
			'seller_order_id' => $order->getTrackingNumber(),
			'store_name' => 'ショップ名',
		));

		if(!$client->success) return array(null, null, "UnknownError");

		// (3) 注文情報を確定
		$client->confirmOrderReference(array(
			'amazon_order_reference_id' => $referenceId
		));

		if(!$client->success) return array(null, null, "PaymentMethodNotAllowed");

		// (4) オーソリをリクエスト
		$response = $client->authorize(array(
			'amazon_order_reference_id' => $referenceId,
			'authorization_amount' => $order->getPrice(),
			'authorization_reference_id' => $order->getTrackingNumber() . "_" . time(),
			'seller_authorization_note' => 'Authorizing payment',
			'transaction_timeout' => 0,
		));

		$result = $response->response;
		if($result["Status"] != 200) return array(null, null, "AmazonRejected");

		preg_match('/<AmazonAuthorizationId>(.*)<\/AmazonAuthorizationId>/', $result["ResponseBody"], $tmp);
		if(!isset($tmp[0])) return array(null, null, "UnknownError");

		// オーソリが成功したか確認
		$amazonAuthorizationId = $tmp[1];

		// (5) 注文を確定
		$response = $client->capture(array(
			'amazon_authorization_id' => $amazonAuthorizationId,
			'capture_amount' => $order->getPrice(),
			'currency_code' => 'JPY',
			'capture_reference_id' => $order->getTrackingNumber() . "_" . time(),
			'seller_capture_note' => '購入が完了しました',
		));

		$result = $response->response;

		// 注文の確定に失敗したらオーソリを取り消して、注文をクローズする
		if($result['Status'] != 200) {
			self::_cancel($client, $referenceId, $amazonAuthorizationId);
			return array(null, null, "TransactionTimedOut or AmazonRejected");
		}

		return array($referenceId, $amazonAuthorizationId, null);
	}

	function cancel($referenceId, $amazonAuthorizationId){
		self::_cancel(self::_client(), $referenceId, $amazonAuthorizationId);
	}

	private function _client(){
		$cnf = AmazonPayUtil::getConfig(false);

		//様々なところで使い回す
		if(!defined("AMAZON_PAY_MERCHANT_ID")) define("AMAZON_PAY_MERCHANT_ID", $cnf["merchant_id"]);

		// (1) Clientインスタンスを作成
		return new Client(array(
			'merchant_id' => AMAZON_PAY_MERCHANT_ID,
			'access_key' => (isset($cnf["access_key_id"])) ? $cnf["access_key_id"] : null,
			'secret_key' => (isset($cnf["secret_access_key"])) ? $cnf["secret_access_key"] : null,
			'client_id' => (isset($cnf["client_id"])) ? $cnf["client_id"] : null,
			'currency_code' => 'jpy',
			'region' => 'jp',
			'sandbox' => (isset($cnf["sandbox"]) && is_bool($cnf["sandbox"])) ? $cnf["sandbox"] : false,
		));
	}

	private function _cancel(Client $client, $referenceId, $amazonAuthorizationId){
		$client->cancelOrderReference(array(
			'merchant_id' => AMAZON_PAY_MERCHANT_ID,
			'amazon_order_reference_id' => $referenceId,
		));
		if(!$client->success) return false;

		$client->closeAuthorization(array(
			'merchant_id' => AMAZON_PAY_MERCHANT_ID,
			'amazon_authorization_id' => $amazonAuthorizationId,
		));
		if(!$client->success) return false;
		return true;
	}

	/** 住所 **/
	//ダメだったけど一応残す
	function address($referenceId){
		$client = self::_client();

		// (2) 注文情報をセット
		$client->SetOrderReferenceDetails(array(
			'merchant_id' => AMAZON_PAY_MERCHANT_ID,
			'amazon_order_reference_id' => $referenceId,
		));

	}
}
