<?php

class HeaderPage extends CMSHTMLPageBase{

	function __construct(){
		parent::__construct();

		HTMLHead::addLink("bootstrap.min.css", array(
			"type" => "text/css",
			"rel" => "stylesheet",
			"href" => SOY2PageController::createRelativeLink("./webapp/pages/files/vendor/bootstrap/css/bootstrap.min.css") . "?" . SOYCMS_BUILD_TIME
		));
		HTMLHead::addLink("metisMenu.min.css", array(
			"type" => "text/css",
			"rel" => "stylesheet",
			"href" => SOY2PageController::createRelativeLink("./webapp/pages/files/vendor/metisMenu/metisMenu.min.css") . "?" . SOYCMS_BUILD_TIME
		));
		HTMLHead::addLink("sb-admin-2.css", array(
			"type" => "text/css",
			"rel" => "stylesheet",
			"href" => SOY2PageController::createRelativeLink("./webapp/pages/files/dist/css/sb-admin-2.css") . "?" . SOYCMS_BUILD_TIME
		));
		HTMLHead::addLink("soycms_cp.css", array(
			"type" => "text/css",
			"rel" => "stylesheet",
			"href" => SOY2PageController::createRelativeLink("./webapp/pages/files/dist/css/soycms_cp.css") . "?" . SOYCMS_BUILD_TIME
		));
		HTMLHead::addLink("morris.css", array(
			"type" => "text/css",
			"rel" => "stylesheet",
			"href" => SOY2PageController::createRelativeLink("./webapp/pages/files/vendor/morrisjs/morris.css") . "?" . SOYCMS_BUILD_TIME
		));
		HTMLHead::addLink("font-awesome.min.css", array(
			"type" => "text/css",
			"rel" => "stylesheet",
			"href" => SOY2PageController::createRelativeLink("./webapp/pages/files/vendor/font-awesome/css/font-awesome.min.css") . "?" . SOYCMS_BUILD_TIME
		));

	}

	function execute(){
		$this->createAdd("header", "HTMLHead", array(
			"title" => (defined("HEAD_TITLE")) ? HEAD_TITLE : CMSUtil::getCMSName(),
			"isEraseHead" => false
		));

		//サイドバーの表示・非表示に伴うメインコンテンツ領域の幅を変える
		$this->addModel("for-narrow-sidebar",array(
				"visible" => ( isset($_COOKIE["admin-hide-side-menu"]) && $_COOKIE["admin-hide-side-menu"] == "true" ),
		));
	}
}
