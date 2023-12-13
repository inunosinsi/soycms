<?php

class PaymentAmazonPayAdminTop extends SOYShopAdminTopBase{

	function getLink(){
		return (self::_isTestMode()) ? SOY2PageController::createLink("Config.Detail?plugin=payment_amazon_pay") : "";
	}

	function getLinkTitle(){
		return (self::_isTestMode()) ? "設定" : "";
	}

	function getTitle(){
		return (self::_isTestMode()) ? "Amazon Pay ワンタイムペイメントモジュール" : "";
	}

	function getContent(){
		if(self::_isTestMode()){
			return "<div class=\"alert alert-danger\">Amazon Pay ワンタイムペイメントモジュールはテストモードです。</div>";
		}
	}

	private function _isTestMode(){
		static $on;
		if(is_null($on)){
			SOY2::import("module.plugins.payment_amazon_pay.util.AmazonPayUtil");
			$cnf = AmazonPayUtil::getConfig();
			$on = (isset($cnf["sandbox"]) && (int)$cnf["sandbox"] === 1);
		}

		return $on;
	}

	function allowDisplay(){
		return AUTH_PLUGIN;
	}
}
SOYShopPlugin::extension("soyshop.admin.top", "payment_amazon_pay", "PaymentAmazonPayAdminTop");
