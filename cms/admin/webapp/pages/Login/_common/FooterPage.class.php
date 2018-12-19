<?php
class FooterPage extends CMSHTMLPageBase{

	var $copyRight = "";

	function __construct(){
		parent::__construct();

		$year = date("Y", SOYCMS_BUILD_TIME);
		if($year > 2007) $year = "2007-" . $year;
		$this->copyRight = $this->getMessage("COMMON_FOOTER_COPYRIGHT", array("YEAR" => $year));

		HTMLHead::addLink("globalpage.css", array(
			"type" => "text/css",
			"rel" => "stylesheet",
			"href" => SOY2PageController::createRelativeLink("./css/global_page/globalpage.css") . "?" . SOYCMS_BUILD_TIME
		));

	}

	function execute(){

		$this->addLabel("copyright", array(
			"html" => $this->copyRight
		));

		//バージョン番号
		$this->addLabel("version",array(
				"text" => SOYCMS_VERSION,
		));
		$this->addLabel("php-version",array(
				"text" => PHP_VERSION
		));

		$this->addLabel("jQuery", array(
			"src" => SOY2PageController::createRelativeLink("./webapp/pages/files/vendor/jquery/jquery.min.js") . "?" . SOYCMS_BUILD_TIME
		));
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
			"src" => SOY2PageController::createRelativeLink("./webapp/pages/files/dist/js/sb-admin-2.js") . "?" . SOYCMS_BUILD_TIME
		));
		$this->addLabel("jQuery-ui", array(
			"src" => SOY2PageController::createRelativeLink("./js/jquery-ui.min.js") . "?" . SOYCMS_BUILD_TIME
		));
		$this->addLabel("soyCommon", array(
			"src" => SOY2PageController::createRelativeLink("./js/common.js") . "?" . SOYCMS_BUILD_TIME
		));

		//Cookie操作用
		$this->addScript("jquery-cookie",array(
				"src" => SOY2PageController::createRelativeLink("./webapp/pages/files/vendor/jquery-cookie/jquery.cookie.js") . "?" . SOYCMS_BUILD_TIME
		));

	}
}
