<?php
class FooterPage extends CMSWebPageBase{

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

		$this->addLabel("cms_name", array(
			"text" => CMSUtil::getCMSName()
		));

		//バージョン番号
		$this->addLabel("version",array(
				"text" => SOYCMS_VERSION,
		));
		$this->addLabel("php-version",array(
				"text" => PHP_VERSION
		));

		$this->addLabel("developer_name", array(
			"text" => CMSUtil::getDeveloperName()
		));

		include_once(dirname(__FILE__) . "/Widget/MemoWidgetComponent.class.php");
		$component = new MemoWidgetComponent();
		$this->addLabel("memo_widget", array(
			"html" => $component->buildWidget()
		));

		if(!defined("SOYCMS_READ_LIBRARY_VIA_CDN")) define("SOYCMS_READ_LIBRARY_VIA_CDN", false);
		$this->addLabel("jQuery", array(
			"src" => (SOYCMS_READ_LIBRARY_VIA_CDN)
						? "https://code.jquery.com/jquery-3.6.0.min.js"
						: SOY2PageController::createRelativeLink("./webapp/pages/files/vendor/jquery/jquery.min.js") . "?" . SOYCMS_BUILD_TIME
		));

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
						: SOY2PageController::createRelativeLink("./webapp/pages/files/dist/js/sb-admin-2.js") . "?" . SOYCMS_BUILD_TIME
		));
		$this->addLabel("jQuery-ui", array(
			"src" => (SOYCMS_READ_LIBRARY_VIA_CDN)
						? "https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"
						: SOY2PageController::createRelativeLink("./js/jquery-ui.min.js") . "?" . SOYCMS_BUILD_TIME
		));
		$this->addLabel("soyCommon", array(
			"src" => SOY2PageController::createRelativeLink("./js/common.js") . "?" . SOYCMS_BUILD_TIME
		));

		//Cookie操作用
		$this->addScript("jquery-cookie",array(
			"src" => (SOYCMS_READ_LIBRARY_VIA_CDN)
						? "https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"
						: SOY2PageController::createRelativeLink("./webapp/pages/files/vendor/jquery-cookie/jquery.cookie.js") . "?" . SOYCMS_BUILD_TIME
		));
	}
}
