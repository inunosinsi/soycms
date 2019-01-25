<?php
MySQL2SQLitePlugin::register();

class MySQL2SQLitePlugin{

	const PLUGIN_ID = "mysql2sqlite";

	private $oldDataSource;	//移行前のMySQLのdsn

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=>"MySQL→SQLite移行プラグイン",
			"description"=>"サイトのデータベースをMySQLからSQLiteへ移行します",
			"author"=>"齋藤毅",
			"url"=>"https://saitodev.co/article/2038",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"0.7"
		));

		//移行前のMySQLのDSNを記録しておく
		if(is_null($this->oldDataSource)){
			$dsn = SOY2DAOConfig::Dsn();
			if(strpos($dsn, "mysql") === 0){
				$this->oldDataSource = $dsn;
				CMSPlugin::savePluginConfig(self::PLUGIN_ID, $this);
			}
		}

		CMSPlugin::addPluginConfigPage(self::PLUGIN_ID,array(
			$this,"config_page"
		));
	}

	function config_page(){
		SOY2::import("site_include.plugin.mysql2sqlite.config.DBMigrationPage");
		$form = SOY2HTMLFactory::createInstance("DBMigrationPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	function getOldDataSource(){
		return $this->oldDataSource;
	}
	function setOldDataSource($oldDataSource){
		$this->oldDataSource = $oldDataSource;
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj){
			$obj = new MySQL2SQLitePlugin();
		}

		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
