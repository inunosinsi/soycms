<?php
class ArrivalNoticeDraftAdminTop extends SOYShopAdminTopBase{

	function notice(){
		if(self::isDraft()){
			$html = "<div class=\"alert alert-info\">管理画面からの注文で下書きがあります。&nbsp;<a href=\"" . SOY2PageController::createLink("Order.Register.Item") . "\" class=\"btn btn-default\">注文の続ける</a></div>";
			return $html;
		}
	}

	private function isDraft(){
		static $isDraft;
		if(is_null($isDraft)) $isDraft = SOY2Logic::createInstance("logic.order.admin.AdminOrderLogic")->isBackupJsonFile();
		return $isDraft;
	}

	function allowDisplay(){
		return (SOYShopAuthUtil::getAuth() != SOYShopAuthUtil::AUTH_STORE_OWNER);
	}
}
SOYShopPlugin::extension("soyshop.admin.top", "arrival_notice_draft", "ArrivalNoticeDraftAdminTop");
