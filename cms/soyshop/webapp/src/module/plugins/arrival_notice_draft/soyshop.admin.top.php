<?php
class ArrivalNoticeDraftAdminTop extends SOYShopAdminTopBase{

	function getLink(){
		return null;
	}

	function getLinkTitle(){
		return null;
	}

	function getTitle(){
		if(self::isDraft()){
			return "管理画面からの注文";
		}
	}

	function getContent(){
		if(self::isDraft()){
			$html = "<p class=\"notice always\">";
			$html .= "管理画面からの注文で下書きがあります。 -&gt; <a href=\"" . SOY2PageController::createLink("Order.Register.Item") . "\">注文の登録</a>";
			$html .= "</p>";
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
