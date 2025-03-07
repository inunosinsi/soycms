<?php

class PayJpAdminTop extends SOYShopAdminTopBase{

	function getLink(){
		return (self::_isTestMode()) ? SOY2PageController::createLink("Config.Detail?plugin=payment_pay_jp") : "";
	}

	function getLinkTitle(){
		return (self::_isTestMode()) ? "設定" : "";
	}

	function getTitle(){
		return (self::_isTestMode()) ? "PAY.JPクレジットカード決済モジュール" : "";
	}

	function getContent(){
		if(self::_isTestMode()){
			return "<div class=\"alert alert-danger\">PAY.JPクレジットカード決済モジュールはテストモードです。</div>";
		}
	}

	private function _isTestMode(){
		static $on;
		if(is_null($on)){
			SOY2::import("module.plugins.payment_pay_jp.util.PayJpUtil");
			$on = PayJpUtil::isTestMode();
		}
		return $on;
	}

	function allowDisplay(){
		return AUTH_PLUGIN;
	}
}
SOYShopPlugin::extension("soyshop.admin.top", "payment_pay_jp", "PayJpAdminTop");
