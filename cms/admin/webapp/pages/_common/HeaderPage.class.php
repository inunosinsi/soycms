<?php

class HeaderPage extends CMSWebPageBase{

	var $title = "";

	function setTitle($title){
		$this->title = $title;
	}

	function __construct(){
		parent::__construct();

		if(!defined("SOYCMS_READ_LIBRARY_VIA_CDN")) define("SOYCMS_READ_LIBRARY_VIA_CDN", false);
		HTMLHead::addLink("bootstrap.min.css", array(
			"type" => "text/css",
			"rel" => "stylesheet",
			"href" => (SOYCMS_READ_LIBRARY_VIA_CDN)
						? "https://stackpath.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
						: SOY2PageController::createRelativeLink("./webapp/pages/files/vendor/bootstrap/css/bootstrap.min.css") . "?" . SOYCMS_BUILD_TIME
		));
		HTMLHead::addLink("metisMenu.min.css", array(
			"type" => "text/css",
			"rel" => "stylesheet",
			"href" => (SOYCMS_READ_LIBRARY_VIA_CDN)
						? "https://cdnjs.cloudflare.com/ajax/libs/metisMenu/3.0.7/metisMenu.css"
						: SOY2PageController::createRelativeLink("./webapp/pages/files/vendor/metisMenu/metisMenu.min.css") . "?" . SOYCMS_BUILD_TIME
		));
		HTMLHead::addLink("sb-admin-2.css", array(
			"type" => "text/css",
			"rel" => "stylesheet",
			"href" => (SOYCMS_READ_LIBRARY_VIA_CDN)
						? "https://cdnjs.cloudflare.com/ajax/libs/startbootstrap-sb-admin-2/3.3.7/css/sb-admin-2.css"
						: SOY2PageController::createRelativeLink("./webapp/pages/files/dist/css/sb-admin-2.css") . "?" . SOYCMS_BUILD_TIME
		));
		HTMLHead::addLink("soycms_cp.css", array(
			"type" => "text/css",
			"rel" => "stylesheet",
			"href" => SOY2PageController::createRelativeLink("./webapp/pages/files/dist/css/soycms_cp.css") . "?" . SOYCMS_BUILD_TIME
		));
		HTMLHead::addLink("morris.css", array(
			"type" => "text/css",
			"rel" => "stylesheet",
			"href" => (SOYCMS_READ_LIBRARY_VIA_CDN)
						? "https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.4.2/morris.min.css"
						: SOY2PageController::createRelativeLink("./webapp/pages/files/vendor/morrisjs/morris.css") . "?" . SOYCMS_BUILD_TIME
		));
		HTMLHead::addLink("font-awesome.min.css", array(
			"type" => "text/css",
			"rel" => "stylesheet",
			"href" => (SOYCMS_READ_LIBRARY_VIA_CDN)
						? "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.min.css"
						: SOY2PageController::createRelativeLink("./webapp/pages/files/vendor/font-awesome/css/font-awesome.min.css") . "?" . SOYCMS_BUILD_TIME
		));

	}

	function execute(){
		$this->createAdd("header", "HTMLHead", array(
			"title" => (isset($this->title) && strlen($this->title)) ? $this->title : CMSUtil::getCMSName(),
			"isEraseHead" => false
		));

		//サイドバーの表示・非表示に伴うメインコンテンツ領域の幅を変える
		$this->addModel("for-narrow-sidebar",array(
				"visible" => ( isset($_COOKIE["admin-hide-side-menu"]) && $_COOKIE["admin-hide-side-menu"] == "true" ),
		));
	}
}
