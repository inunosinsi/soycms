<?php

class DropboxBackupPlugin{

	const PLUGIN_ID = "dropbox_backup";

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){

		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=>"Dropboxバックアッププラグイン",
			"description"=>"Dropboxを利用してサイトのバックアップを行います",
			"author"=>"齋藤毅",
			"modifier"=>"Tsuyoshi Saito",
			"url"=>"https://saitodev.co",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"0.1"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			CMSPlugin::addPluginConfigPage(self::PLUGIN_ID,array($this,"config"));
		}
	}

	/**
	 * 設定画面表示
	 * @return HTML
	 */
	function config(){
		SOY2::import("site_include.plugin.dropbox_backup.config.DropboxBackupConfigPage");
		$form = SOY2HTMLFactory::createInstance("DropboxBackupConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)){
			$obj = new DropboxBackupPlugin();
		}
		CMSPlugin::addPlugin(self::PLUGIN_ID,array($obj,"init"));
	}
}

DropboxBackupPlugin::register();
