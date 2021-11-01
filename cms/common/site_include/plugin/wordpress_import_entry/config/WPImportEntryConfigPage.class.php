<?php

class WPImportEntryConfigPage extends WebPage{

	private $pluginObj;

	function __construct(){
		SOY2::import("site_include.plugin.wordpress_import_entry.util.WordPressImportEntryUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			if(isset($_POST["update"])){
				WordPressImportEntryUtil::saveConfig($_POST["DB"]);
				CMSPlugin::redirectConfigPage();
			}

			if(isset($_POST["import"])){
				SOY2Logic::createInstance("site_include.plugin.wordpress_import_entry.logic.Wp2SoyLogic")->execute();
			}

			CMSPlugin::redirectConfigPage();
		}
	}

	function execute(){
		parent::__construct();

		self::_exeForm();
		self::_buildConfigForm();
	}

	private function _exeForm(){
		DisplayPlugin::toggle("import_area", WordPressImportEntryUtil::isDBConfig());

		$this->addForm("import_form");
	}

	private function _buildConfigForm(){
		$this->addForm("form");

		$cnf = WordPressImportEntryUtil::getConfig();
		foreach(array("name", "user", "password", "host") as $t){
			$this->addInput($t, array(
				"name" => "DB[" . $t . "]",
				"value" => $cnf[$t]
			));
		}
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
