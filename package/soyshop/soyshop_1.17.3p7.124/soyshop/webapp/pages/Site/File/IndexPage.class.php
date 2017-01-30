<?php

class IndexPage extends WebPage{
	
	private $cmsElFinderPath;
	
	function doPost(){
	}

	function __construct(){
		WebPage::__construct();
		
		$this->cmsElFinderPath = str_replace("/soyshop/", "/soycms/", SOY2PageController::createRelativeLink("./js/"));
		
		$this->addLabel("connector_file_path", array(
			"text" => $this->cmsElFinderPath . "elfinder/php/connector.php?shop_id=" . SOYSHOP_ID
		));
		
		DisplayPlugin::toggle("normal_template_area", (!isset($_GET["display_mode"]) || $_GET["display_mode"] != "free"));
		DisplayPlugin::toggle("free_template_area", (isset($_GET["display_mode"]) && $_GET["display_mode"] == "free"));
	}
	
	/** ここからelfinder **/
	function getCSS(){
		$root = $this->cmsElFinderPath;
		return array(
			$root . "elfinder/jquery/ui-themes/smoothness/jquery-ui-1.10.1.custom.css",
			$root . "elfinder/css/common.css",
			$root . "elfinder/css/dialog.css",
			$root . "elfinder/css/toolbar.css",
			$root . "elfinder/css/navbar.css",
			$root . "elfinder/css/statusbar.css",
			$root . "elfinder/css/contextmenu.css",
			$root . "elfinder/css/cwd.css",
			$root . "elfinder/css/quicklook.css",
			$root . "elfinder/css/commands.css",
			$root . "elfinder/css/fonts.css",
			$root . "elfinder/css/theme.css"
		);
	}

	function getScripts(){
		$root = $this->cmsElFinderPath;
		return array(
			$root . "elfinder/jquery/jquery-1.9.1.min.js",
			$root . "elfinder/jquery/jquery-ui-1.10.1.custom.min.js",
			$root . "elfinder/js/elFinder.js",
			$root . "elfinder/js/elFinder.version.js",
			$root . "elfinder/js/jquery.elfinder.js",
			$root . "elfinder/js/elFinder.resources.js",
			$root . "elfinder/js/elFinder.options.js",
			$root . "elfinder/js/elFinder.history.js",
			$root . "elfinder/js/elFinder.command.js",
			$root . "elfinder/js/ui/overlay.js",
			$root . "elfinder/js/ui/workzone.js",
			$root . "elfinder/js/ui/navbar.js",
			$root . "elfinder/js/ui/dialog.js",
			$root . "elfinder/js/ui/tree.js",
			$root . "elfinder/js/ui/cwd.js",
			$root . "elfinder/js/ui/toolbar.js",
			$root . "elfinder/js/ui/button.js",
			$root . "elfinder/js/ui/uploadButton.js",
			$root . "elfinder/js/ui/viewbutton.js",
			$root . "elfinder/js/ui/searchbutton.js",
			$root . "elfinder/js/ui/sortbutton.js",
			$root . "elfinder/js/ui/panel.js",
			$root . "elfinder/js/ui/contextmenu.js",
			$root . "elfinder/js/ui/path.js",
			$root . "elfinder/js/ui/stat.js",
			$root . "elfinder/js/ui/places.js",
			$root . "elfinder/js/commands/back.js",
			$root . "elfinder/js/commands/forward.js",
			$root . "elfinder/js/commands/reload.js",
			$root . "elfinder/js/commands/up.js",
			$root . "elfinder/js/commands/home.js",
			$root . "elfinder/js/commands/copy.js",
			$root . "elfinder/js/commands/cut.js",
			$root . "elfinder/js/commands/paste.js",
			$root . "elfinder/js/commands/open.js",
			$root . "elfinder/js/commands/rm.js",
			$root . "elfinder/js/commands/info.js",
			$root . "elfinder/js/commands/duplicate.js",
			$root . "elfinder/js/commands/rename.js",
			$root . "elfinder/js/commands/help.js",
			$root . "elfinder/js/commands/getfile.js",
			$root . "elfinder/js/commands/mkdir.js",
			$root . "elfinder/js/commands/mkfile.js",
			$root . "elfinder/js/commands/upload.js",
			$root . "elfinder/js/commands/download.js",
			$root . "elfinder/js/commands/edit.js",
			$root . "elfinder/js/commands/quicklook.js",
			$root . "elfinder/js/commands/quicklook.plugins.js",
			$root . "elfinder/js/commands/extract.js",
			$root . "elfinder/js/commands/archive.js",
			$root . "elfinder/js/commands/search.js",
			$root . "elfinder/js/commands/view.js",
			$root . "elfinder/js/commands/resize.js",
			$root . "elfinder/js/commands/sort.js",	
			$root . "elfinder/js/commands/netmount.js",	
			$root . "elfinder/js/i18n/elfinder.ar.js",
			$root . "elfinder/js/i18n/elfinder.bg.js",
			$root . "elfinder/js/i18n/elfinder.ca.js",
			$root . "elfinder/js/i18n/elfinder.cs.js",
			$root . "elfinder/js/i18n/elfinder.de.js",
			$root . "elfinder/js/i18n/elfinder.el.js",
			$root . "elfinder/js/i18n/elfinder.en.js",
			$root . "elfinder/js/i18n/elfinder.es.js",
			$root . "elfinder/js/i18n/elfinder.fa.js",
			$root . "elfinder/js/i18n/elfinder.fr.js",
			$root . "elfinder/js/i18n/elfinder.hu.js",
			$root . "elfinder/js/i18n/elfinder.it.js",
			$root . "elfinder/js/i18n/elfinder.jp.js",
			$root . "elfinder/js/i18n/elfinder.ko.js",
			$root . "elfinder/js/i18n/elfinder.nl.js",
			$root . "elfinder/js/i18n/elfinder.no.js",
			$root . "elfinder/js/i18n/elfinder.pl.js",
			$root . "elfinder/js/i18n/elfinder.pt_BR.js",
			$root . "elfinder/js/i18n/elfinder.ru.js",
			$root . "elfinder/js/i18n/elfinder.sl.js",
			$root . "elfinder/js/i18n/elfinder.sv.js",
			$root . "elfinder/js/i18n/elfinder.tr.js",
			$root . "elfinder/js/i18n/elfinder.zh_CN.js",
			$root . "elfinder/js/i18n/elfinder.zh_TW.js",
			$root . "elfinder/js/i18n/elfinder.vi.js",
			$root . "elfinder/js/jquery.dialogelfinder.js",
			$root . "elfinder/js/proxy/elFinderSupportVer1.js"
		);
	}
}
?>