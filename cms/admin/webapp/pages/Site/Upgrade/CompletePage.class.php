<?php
SOY2::import("domain.admin.Site");

class CompletePage extends CMSWebPageBase{

	function __construct(){
		WebPage::__construct();

		//初期管理者のみ
		if(!UserInfoUtil::isDefaultUser()){
			SOY2PageController::jump("");
		}
	}
}
