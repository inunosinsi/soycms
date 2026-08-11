<?php

class PayJpAdminRecurringTop extends SOYShopAdminTopBase {

	function getLink(){
		return (self::_isTestMode()) ? SOY2PageController::createLink("Config.Detail?plugin=payment_pay_jp_recurring") : "";
	}

	function getLinkTitle(){
		return (self::_isTestMode()) ? "設定" : "";
	}

	function getTitle(){
		return (self::_isTestMode()) ? "PAY.JP定期課金モジュール" : "";
	}

	function getContent(){
		if(self::_isTestMode()){
			return "<div class=\"alert alert-danger\">PAY.JP定期課金モジュールはテストモードです。</div>";
		}
	}

	private function _isTestMode(){
		static $on;
		if(is_null($on)){
			SOY2::import("module.plugins.payment_pay_jp_recurring.util.PayJpRecurringUtil");
			$cnf = PayJpRecurringUtil::getConfig();
			$on = (isset($cnf["sandbox"]) && (int)$cnf["sandbox"] === 1);
		}

		return $on;
	}

	function allowDisplay(){
		return AUTH_PLUGIN;
	}
}
SOYShopPlugin::extension("soyshop.admin.top", "payment_pay_jp_recurring", "PayJpAdminRecurringTop");
