<?php
SOY2::import("domain.admin.Site");

class CompletePage extends CMSWebPageBase{

	function CompletePage(){
		WebPage::WebPage();

		//初期管理者のみ
		if(!UserInfoUtil::isDefaultUser()){
			SOY2PageController::jump("");
		}
	}
}
