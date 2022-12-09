<?php

class IndexPage extends WebPage{

	private $cmsElFinderPath;

	function doPost(){
	}

	function __construct(){
		parent::__construct();

		if(!defined("SOYCMS_ADMIN_URI")) define("SOYCMS_ADMIN_URI", "soycms");
		if(!defined("SOYSHOP_ADMIN_URI")) define("SOYSHOP_ADMIN_URI", "soyshop");
		$this->cmsElFinderPath = str_replace("/" . SOYSHOP_ADMIN_URI . "/", "/" . SOYCMS_ADMIN_URI . "/", SOY2PageController::createRelativeLink("./js/"));

		$this->addLabel("base_dir_path", array(
			"text" => $this->cmsElFinderPath . "elfinder/"
		));

		$this->addLabel("connector_file_path", array(
			"text" => $this->cmsElFinderPath . "elfinder/php/connector.php?shop_id=" . SOYSHOP_ID
		));


		DisplayPlugin::toggle("normal_template_area", (!isset($_GET["display_mode"]) || $_GET["display_mode"] != "free"));
		DisplayPlugin::toggle("free_template_area", (isset($_GET["display_mode"]) && $_GET["display_mode"] == "free"));
	}

	function getBreadcrumb(){
		return BreadcrumbComponent::build("ファイル管理");
	}

	/** ここからelfinder **/
	function getCSS(){
		$root = $this->cmsElFinderPath;
		return array(
			$root . "elfinder/jquery/jquery-ui-1.12.1.min.css",
			$root . "elfinder/css/elfinder.min.css",
			$root . "elfinder/css/theme.css",
		);
	}

	function getScripts(){
		$root = $this->cmsElFinderPath;
		return array(
			$root . "elfinder/jquery/jquery-3.2.1.min.js",
			$root . "elfinder/jquery/jquery-ui-1.12.1.min.js",
			$root . "elfinder/js/elfinder.min.js",
			$root . "elfinder/js/extras/editors.default.min.js",
		);
	}
}
