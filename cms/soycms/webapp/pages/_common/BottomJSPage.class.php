<?php

class BottomJSPage extends CMSHTMLPageBase{

	public function execute(){

		$this->addLabel("bootstrap", array(
				"src" => SOY2PageController::createRelativeLink("./webapp/pages/files/vendor/bootstrap/js/bootstrap.min.js") . "?" . SOYCMS_BUILD_TIME
		));
		$this->addLabel("metis", array(
				"src" => SOY2PageController::createRelativeLink("./webapp/pages/files/vendor/metisMenu/metisMenu.min.js") . "?" . SOYCMS_BUILD_TIME
		));
		$this->addLabel("raphael", array(
				"src" => SOY2PageController::createRelativeLink("./webapp/pages/files/vendor/raphael/raphael.min.js") . "?" . SOYCMS_BUILD_TIME
		));
		$this->addLabel("morris", array(
				"src" => SOY2PageController::createRelativeLink("./webapp/pages/files/vendor/morrisjs/morris.min.js") . "?" . SOYCMS_BUILD_TIME
		));
		$this->addLabel("sbAdmin", array(
				"src" => SOY2PageController::createRelativeLink("./webapp/pages/files/dist/js/sb-admin-2.min.js") . "?" . SOYCMS_BUILD_TIME
		));
		$this->addLabel("soyCommon", array(
				"src" => SOY2PageController::createRelativeLink("./webapp/pages/files/dist/js/soycms-common.js") . "?" . SOYCMS_BUILD_TIME
		));
		$lang = defined("SOYCMS_LANGUAGE") ? SOYCMS_LANGUAGE : "ja";
		$this->addLabel("soyLang", array(
				"src" => SOY2PageController::createRelativeLink("./js/lang/".$lang.".js") . "?" . SOYCMS_BUILD_TIME
		));
		$this->addLabel("soycmsCheckSite", array(
				"html" => "soycms_check_site('".UserInfoUtil::getSite()->getId()."','".SOY2PageController::createLink("Common.Check")."');",
				"visible" => !defined("SOYCMS_ASP_MODE"),
		));

		//Cookie操作用
		$this->addScript("jquery-cookie",array(
				"src" => SOY2PageController::createRelativeLink("./webapp/pages/files/vendor/jquery-cookie/jquery.cookie.js") . "?" . SOYCMS_BUILD_TIME
		));
	}
}
