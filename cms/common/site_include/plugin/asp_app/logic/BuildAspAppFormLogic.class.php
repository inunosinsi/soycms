<?php

class BuildAspAppFormLogic extends SOY2LogicBase {

	function __construct(){
		SOY2::import("site_include.plugin.asp_app.util.AspAppUtil");
	}

	function build(){

		AspAppUtil::getPageUri();

		switch(AspAppUtil::getPageType()){
			case AspAppUtil::MODE_REGISTER:
				$pageClass = "AspAppUserRegisterPage";
				break;
			case AspAppUtil::MODE_CONFIRM:
				$pageClass = "AspAppUserConfirmPage";
				break;
			case AspAppUtil::MODE_PRE_REGISTRATION:
				$pageClass = "AspAppUserPreRegistrationPage";
				break;
			case AspAppUtil::MODE_COMPLETE:
				$pageClass = "AspAppUserCompletePage";
				break;
			case AspAppUtil::MODE_DIRECT_REGISTRATION:
				$pageClass = "AspAppUserDirectRegistrationPage";
				break;
		}

		SOY2::import("site_include.plugin.asp_app.page." . $pageClass);
		$page = SOY2HTMLFactory::createInstance($pageClass);
		$page->execute();
		return $page->getObject();
	}
}
