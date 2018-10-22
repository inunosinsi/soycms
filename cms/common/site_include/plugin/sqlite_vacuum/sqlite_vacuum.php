<?php

class SqliteVacuumPlugin{

	const PLUGIN_ID = "sqlite_vacuum";

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=>"SQLite VACUUM",
			"description"=>"記事作成時にSQLite VACUUMを実行する",
			"author"=>"saitodev.co",
			"url"=>"https://saitodev.co/",
			"mail"=>"tsuyoshi saitodev.co",
			"version"=>"0.1"
		));

		//管理側
		if(!defined("_SITE_ROOT_")){

			// CMSPlugin::addPluginConfigPage(self::PLUGIN_ID, array(
			// 	$this,"config_page"
			// ));

			CMSPlugin::setEvent('onEntryCreate', self::PLUGIN_ID, array($this, "onEntryUpdate"));
		}
	}

	/**
	 * 設定画面の表示
	 */
	//function config_page(){}

	function onEntryUpdate($arg){
		//記事作成時に常に実行
		$res = exec("sqlite3 " . UserInfoUtil::getSiteDirectory() . ".db/sqlite.db vacuum");
	}

	public static function register(){

		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)){
			$obj = new SqliteVacuumPlugin();
		}

		CMSPlugin::addPlugin(self::PLUGIN_ID,array($obj,"init"));
	}
}

SqliteVacuumPlugin::register();
