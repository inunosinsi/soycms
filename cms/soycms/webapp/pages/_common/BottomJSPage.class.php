<?php

class BottomJSPage extends CMSHTMLPageBase{

	public function execute(){
		if(!defined("SOYCMS_READ_LIBRARY_VIA_CDN")) define("SOYCMS_READ_LIBRARY_VIA_CDN", false);
		$this->addLabel("bootstrap", array(
				"src" => (SOYCMS_READ_LIBRARY_VIA_CDN)
							? "https://stackpath.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
							: SOY2PageController::createRelativeLink("./webapp/pages/files/vendor/bootstrap/js/bootstrap.min.js") . "?" . SOYCMS_BUILD_TIME
		));
		$this->addLabel("metis", array(
				"src" => (SOYCMS_READ_LIBRARY_VIA_CDN)
							? "https://cdnjs.cloudflare.com/ajax/libs/metisMenu/3.0.7/metisMenu.min.js"
							: SOY2PageController::createRelativeLink("./webapp/pages/files/vendor/metisMenu/metisMenu.min.js") . "?" . SOYCMS_BUILD_TIME
		));
		$this->addLabel("raphael", array(
				"src" => (SOYCMS_READ_LIBRARY_VIA_CDN)
							? "https://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.1/raphael-min.js"
							: SOY2PageController::createRelativeLink("./webapp/pages/files/vendor/raphael/raphael.min.js") . "?" . SOYCMS_BUILD_TIME
		));
		$this->addLabel("morris", array(
				"src" => (SOYCMS_READ_LIBRARY_VIA_CDN)
							? "https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.4.2/morris.min.js"
							: SOY2PageController::createRelativeLink("./webapp/pages/files/vendor/morrisjs/morris.min.js") . "?" . SOYCMS_BUILD_TIME
		));
		$this->addLabel("sbAdmin", array(
				"src" => (SOYCMS_READ_LIBRARY_VIA_CDN)
							? "https://cdnjs.cloudflare.com/ajax/libs/startbootstrap-sb-admin-2/3.3.7/js/sb-admin-2.min.js"
							: SOY2PageController::createRelativeLink("./webapp/pages/files/dist/js/sb-admin-2.min.js") . "?" . SOYCMS_BUILD_TIME
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
				"src" => (SOYCMS_READ_LIBRARY_VIA_CDN)
						? "https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"
						: SOY2PageController::createRelativeLink("./webapp/pages/files/vendor/jquery-cookie/jquery.cookie.js") . "?" . SOYCMS_BUILD_TIME
		));
	}
}
