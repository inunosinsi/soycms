<?php

class BuildAspFormLogic extends SOY2LogicBase {

	function __construct(){
		SOY2::import("site_include.plugin.asp.util.AspUtil");
	}

	function build(){

		AspUtil::getPageUri();

		switch(AspUtil::getPageType()){
			case AspUtil::MODE_REGISTER:
				$pageClass = "AspUserRegisterPage";
				break;
			case AspUtil::MODE_CONFIRM:
				$pageClass = "AspUserConfirmPage";
				break;
			case AspUtil::MODE_PRE_REGISTRATION:
				$pageClass = "AspUserPreRegistrationPage";
				break;
			case AspUtil::MODE_COMPLETE:
				$pageClass = "AspUserCompletePage";
				break;
		}

		SOY2::import("site_include.plugin.asp.page." . $pageClass);
		$page = SOY2HTMLFactory::createInstance($pageClass);
		$page->execute();
		return $page->getObject();
	}
}
