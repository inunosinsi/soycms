<?php

class AmazonPayAddressPage extends WebPage{

	function __construct(){
		SOY2::import("module.plugins.payment_amazon_pay.util.AmazonPayUtil");
	}

	function doPost(){}

	function execute(){
		//念の為にアクセストークンを取得しておく
		if(isset($_GET["access_token"])) $_SESSION["access_token"] = $_GET["access_token"];

		parent::__construct();

		//エラーメッセージ
		$err = self::_getErrorMessage();

		DisplayPlugin::toggle("error", strlen($err));
		$this->addLabel("error_message", array(
			"text" => $err
		));

		$cnf = AmazonPayUtil::getConfig(false);

		$this->addForm("form");

		$this->addLabel("merchant_id", array(
			"text" => $cnf["merchant_id"]
		));

		$this->addLabel("client_id", array(
			"text" => $cnf["client_id"]
		));

		$this->addLabel("action_url", array(
			"text" => AmazonPayUtil::getActionUrl()
		));

		$this->addLink("back_link", array(
			"link" => AmazonPayUtil::getBackUrl()
		));

		if(isset($cnf["sandbox"]) && $cnf["sandbox"]){	//テスト
			$widgetJs = "https://static-fe.payments-amazon.com/OffAmazonPayments/jp/sandbox/lpa/js/Widgets.js";
		}else{	//本番
			$widgetJs = "https://static-fe.payments-amazon.com/OffAmazonPayments/jp/lpa/js/Widgets.js";
		}
		$this->addModel("widget_js", array(
			"attr:src" => $widgetJs
		));
	}

	private function _getErrorMessage(){
		if(isset($_POST["amazonPayErrorMessage"]) && strlen($_POST["amazonPayErrorMessage"])) return $_POST["amazonPayErrorMessage"];

		$cart = CartLogic::getCart();
		$err = $cart->getErrorMessage("amazon_pay_error");
		if(isset($err)) {
			$cart->removeErrorMessage("amazon_pay_error");
			$cart->save();
			return $err;
		}
		return null;
	}
}
