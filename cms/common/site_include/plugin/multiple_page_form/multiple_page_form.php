<?php
MultiplePageFormPlugin::register();
class MultiplePageFormPlugin{

	const PLUGIN_ID = "multiple_page_form";

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name" => "複数ページフォームプラグイン",
			"description" => "複数ページにまたがるフォームページを設置します",
			"author"=>"齋藤毅",
			"url"=>"https://saitodev.co/article/3302",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"0.5.2"
		));

		CMSPlugin::addPluginConfigPage(self::PLUGIN_ID,array(
			$this,"config_page"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			if(defined("_SITE_ROOT_")){

			}else{

			}
		}
	}

	function config_page(){
		if(isset($_GET["detail"])){
			SOY2::import("site_include.plugin.multiple_page_form.util.MultiplePageFormUtil");
			MultiplePageFormUtil::isJson($_GET["detail"]);
			$cnf = MultiplePageFormUtil::readJson($_GET["detail"]);
			if(!isset($cnf["type"])) SOY2PageController::jump("Plugin.Config?multiple_page_form");

			switch($cnf["type"]){
				case MultiplePageFormUtil::TYPE_TEXT:
					SOY2::import("site_include.plugin." . self::PLUGIN_ID . ".config." . $cnf["type"] . ".MPFTextConfigPage");
					$form = SOY2HTMLFactory::createInstance("MPFTextConfigPage");
					break;
				case MultiplePageFormUtil::TYPE_CHOICE:
					SOY2::import("site_include.plugin." . self::PLUGIN_ID . ".config." . $cnf["type"] . ".MPFChoiceConfigPage");
					$form = SOY2HTMLFactory::createInstance("MPFChoiceConfigPage");
					break;
				case MultiplePageFormUtil::TYPE_FORM:
					SOY2::import("site_include.plugin." . self::PLUGIN_ID . ".config." . $cnf["type"] . ".MPFFormConfigPage");
					$form = SOY2HTMLFactory::createInstance("MPFFormConfigPage");
					break;
				case MultiplePageFormUtil::TYPE_EXTEND:
					SOY2::import("site_include.plugin." . self::PLUGIN_ID . ".config." . $cnf["type"] . ".MPFExtendConfigPage");
					$form = SOY2HTMLFactory::createInstance("MPFExtendConfigPage");
					break;
				case MultiplePageFormUtil::TYPE_CONFIRM:
					$type = "confirm";
					SOY2::import("site_include.plugin." . self::PLUGIN_ID . ".config." . $type . ".MPFConfirmConfigPage");
					$form = SOY2HTMLFactory::createInstance("MPFConfirmConfigPage");
					break;
				case MultiplePageFormUtil::TYPE_CONFIRM_CHOICE:
					SOY2::import("site_include.plugin." . self::PLUGIN_ID . ".config." . $cnf["type"] . ".MPFConfirmAndChoiceConfigPage");
					$form = SOY2HTMLFactory::createInstance("MPFConfirmAndChoiceConfigPage");
					break;
				case MultiplePageFormUtil::TYPE_COMPLETE:
					SOY2::import("site_include.plugin." . self::PLUGIN_ID . ".config." . $cnf["type"] . ".MPFCompleteConfigPage");
					$form = SOY2HTMLFactory::createInstance("MPFCompleteConfigPage");
					break;
			}

			$form->setHash($_GET["detail"]);
		}else{
			if(isset($_GET["connect"])){
				SOY2::import("site_include.plugin." . self::PLUGIN_ID . ".config.connect.SOYInquiryConnectPage");
				$form = SOY2HTMLFactory::createInstance("SOYInquiryConnectPage");
			}else if(isset($_GET["setting"])){
				SOY2::import("site_include.plugin." . self::PLUGIN_ID . ".config.setting.MPFSettingPage");
				$form = SOY2HTMLFactory::createInstance("MPFSettingPage");
			}else{
				SOY2::import("site_include.plugin." . self::PLUGIN_ID . ".config.MultiplePageFormConfigPage");
				$form = SOY2HTMLFactory::createInstance("MultiplePageFormConfigPage");
			}

		}

		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj) $obj = new MultiplePageFormPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
