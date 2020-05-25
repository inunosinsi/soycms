<?php

CustomfieldMigrationPlugin::register();
class CustomfieldMigrationPlugin {

	const PLUGIN_ID = "customfield_migration";

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID, array(
			"name" => "カスタムフィールドデータ移行プラグイン",
			"description" => "カスタムフィールドからカスタムサーチフィールドにデータを移行する",
			"author" => "齋藤毅",
			"url" => "https://saitodev.co",
			"mail" => "tsuyosho@saitodev.co",
			"version" => "0.1"
		));

		CMSPlugin::addPluginConfigPage(self::PLUGIN_ID, array(
			$this,"config_page"
		));
	}

	function config_page($message){
		SOY2::import("site_include.plugin.customfield_migration.config.CustomfieldMigrationConfigPage");
		$form = SOY2HTMLFactory::createInstance("CustomfieldMigrationConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)) $obj = new CustomfieldMigrationPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
