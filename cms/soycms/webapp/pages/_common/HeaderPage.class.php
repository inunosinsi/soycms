<?php

class HeaderPage extends CMSWebPageBase{

	var $title = "";

	function __construct(){
		parent::__construct();

		if(!defined("SOYCMS_READ_LIBRARY_VIA_CDN")) define("SOYCMS_READ_LIBRARY_VIA_CDN", false);

		$this->title = " - ".UserInfoUtil::getSite()->getSiteName()." (".UserInfoUtil::getSite()->getSiteId().")";

		$this->buildCssLink();

		HTMLHead::addScript("jquery.min.js",array(
			"src" => (SOYCMS_READ_LIBRARY_VIA_CDN)
						? "https://code.jquery.com/jquery-3.6.0.min.js"
						: SOY2PageController::createRelativeLink("../soycms/webapp/pages/files/vendor/jquery/jquery.min.js") . "?" . SOYCMS_BUILD_TIME
		));

		HTMLHead::addScript("jquery-ui.min.js",array(
			"src" => (SOYCMS_READ_LIBRARY_VIA_CDN)
						? "https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"
						: SOY2PageController::createRelativeLink("../soycms/webapp/pages/files/vendor/jquery-ui/jquery-ui.min.js") . "?" . SOYCMS_BUILD_TIME
		));
	}

	function execute(){
		$this->createAdd("header", "HTMLHead", array(
			"title" => $this->title,
			"isEraseHead" => false
		));

		//サイドバーの表示・非表示に伴うメインコンテンツ領域の幅を変える
		$this->addModel("for-narrow-sidebar",array(
				"visible" => ( isset($_COOKIE["soycms-hide-side-menu"]) && $_COOKIE["soycms-hide-side-menu"] == "true" ),
		));
	}

	private function buildCssLink(){
		HTMLHead::addLink("bootstrap.min.css", array(
				"type" => "text/css",
				"rel" => "stylesheet",
				"href" => (SOYCMS_READ_LIBRARY_VIA_CDN)
							? "https://stackpath.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
							: SOY2PageController::createRelativeLink("../soycms/webapp/pages/files/vendor/bootstrap/css/bootstrap.min.css") . "?" . SOYCMS_BUILD_TIME
		));
		HTMLHead::addLink("metisMenu.min.css", array(
				"type" => "text/css",
				"rel" => "stylesheet",
				"href" => (SOYCMS_READ_LIBRARY_VIA_CDN)
							? "https://cdnjs.cloudflare.com/ajax/libs/metisMenu/3.0.7/metisMenu.css"
							: SOY2PageController::createRelativeLink("../soycms/webapp/pages/files/vendor/metisMenu/metisMenu.min.css") . "?" . SOYCMS_BUILD_TIME
		));
		HTMLHead::addLink("sb-admin-2.css", array(
				"type" => "text/css",
				"rel" => "stylesheet",
				"href" => (SOYCMS_READ_LIBRARY_VIA_CDN)
							? "https://cdnjs.cloudflare.com/ajax/libs/startbootstrap-sb-admin-2/3.3.7/css/sb-admin-2.css"
							: SOY2PageController::createRelativeLink("../soycms/webapp/pages/files/dist/css/sb-admin-2.css") . "?" . SOYCMS_BUILD_TIME
		));
		HTMLHead::addLink("soycms_cp.css", array(
				"type" => "text/css",
				"rel" => "stylesheet",
				"href" => SOY2PageController::createRelativeLink("../soycms/webapp/pages/files/dist/css/soycms_cp.css") . "?" . SOYCMS_BUILD_TIME
		));
		HTMLHead::addLink("morris.css", array(
				"type" => "text/css",
				"rel" => "stylesheet",
				"href" => (SOYCMS_READ_LIBRARY_VIA_CDN)
							? "https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.4.2/morris.min.css"
							: SOY2PageController::createRelativeLink("../soycms/webapp/pages/files/vendor/morrisjs/morris.css") . "?" . SOYCMS_BUILD_TIME
		));
		HTMLHead::addLink("font-awesome.min.css", array(
				"type" => "text/css",
				"rel" => "stylesheet",
				"href" => (SOYCMS_READ_LIBRARY_VIA_CDN)
							? "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.min.css"
							: SOY2PageController::createRelativeLink("../soycms/webapp/pages/files/vendor/font-awesome/css/font-awesome.min.css") . "?" . SOYCMS_BUILD_TIME
		));
		HTMLHead::addLink("jquery-ui.min.css",array(
				"rel" => "stylesheet",
				"type" => "text/css",
				"href" => (SOYCMS_READ_LIBRARY_VIA_CDN)
							? "https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css"
							: SOY2PageController::createRelativeLink("../soycms/webapp/pages/files/vendor/jquery-ui/jquery-ui.min.css") . "?" . SOYCMS_BUILD_TIME
		));
	}
}
