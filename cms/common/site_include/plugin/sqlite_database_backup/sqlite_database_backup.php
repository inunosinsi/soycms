<?php

class SqliteDatabaseBackupPlugin{

	const PLUGIN_ID = "sqlite_database_backup";

	//バックアップディレクトリ
	//.db以下に置かないと外部からダウンロードできるようになってよくない
	public $backupDir = ".db/backup/";

	function init(){
		CMSPlugin::addPluginMenu(SqliteDatabaseBackupPlugin::PLUGIN_ID,array(
			"name"=>"SQLiteデータベースバックアッププラグイン",
			"type" => Plugin::TYPE_DB,
			"description"=>"SQLiteのデータベースファイルのバックアップ用のプラグインです。データベースロックの解除にも対応しています。",
			"author"=>"株式会社Brassica",
			"url"=>"https://brassica.jp/",
			"mail"=>"soycms@soycms.net",
			"version"=>"1.1"
		));
		CMSPlugin::addPluginConfigPage(SqliteDatabaseBackupPlugin::PLUGIN_ID,array(
			$this,"config_page"
		));
	}

	/**
	 * 設定画面の表示
	 */
	function config_page(){
		SOY2::import("site_include.plugin.sqlite_database_backup.config.SDBConfigPage");
		$form = SOY2HTMLFactory::createInstance("SDBConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(SqliteDatabaseBackupPlugin::PLUGIN_ID);
		if(is_null($obj)) $obj = new SqliteDatabaseBackupPlugin();
		CMSPlugin::addPlugin(SqliteDatabaseBackupPlugin::PLUGIN_ID,array($obj,"init"));
	}
}

SqliteDatabaseBackupPlugin::register();
