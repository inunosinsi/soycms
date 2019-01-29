<?php
class ArrivalNoticeDraftAdminTop extends SOYShopAdminTopBase{

	function notice(){
		if(self::isDraft()){
			$html = "管理画面からの注文で下書きがあります。&nbsp;<a href=\"" . SOY2PageController::createLink("Order.Register.Item") . "\" class=\"button\">注文の続ける</a>";
			return $html;
		}
	}

	private function isDraft(){
		static $isDraft;
		if(is_null($isDraft)) $isDraft = SOY2Logic::createInstance("logic.order.admin.AdminOrderLogic")->isBackupJsonFile();
		return $isDraft;
	}
}
SOYShopPlugin::extension("soyshop.admin.top", "arrival_notice_draft", "ArrivalNoticeDraftAdminTop");
