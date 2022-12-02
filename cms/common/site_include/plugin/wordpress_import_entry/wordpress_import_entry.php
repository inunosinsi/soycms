<?php

WordPressImportEntryPlugin::register();

class WordPressImportEntryPlugin{

	const PLUGIN_ID = "wordpress_import_entry";

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name" => "WordPress記事インポートプラグイン",
			"type" => Plugin::TYPE_DB,
			"description" => "",
			"author" => "齋藤毅",
			"url" => "https://saitodev.co",
			"mail" => "tsuyoshi@saitodev.co",
			"version" => "0.5"
		));

		//プラグイン アクティブ
		if(CMSPlugin::activeCheck($this->getId())){
			CMSPlugin::addPluginConfigPage(self::PLUGIN_ID ,array(
				$this, "config_page"
			));

			//管理側
			// if(!defined("_SITE_ROOT_")){
			// 	//何もしない
			// //公開側
			// }else{
			// 	//
			// }
		}
	}

	function config_page(){
		SOY2::import("site_include.plugin.wordpress_import_entry.config.WPImportEntryConfigPage");
		$form = SOY2HTMLFactory::createInstance("WPImportEntryConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj) $obj = new WordPressImportEntryPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
