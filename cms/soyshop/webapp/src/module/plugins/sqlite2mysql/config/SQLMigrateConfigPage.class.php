<?php

class SQLMigrateConfigPage extends WebPage {

	private $configObj;
	private $config;

	function __construct(){
		SOY2::import("module.plugins.sqlite2mysql.util.SQLMigrateUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			if(isset($_POST["Config"])){
				SQLMigrateUtil::saveConfig($_POST["Config"]);
				$this->configObj->redirect("updated");
			}

			if(isset($_POST["migrate"])){
				SOY2Logic::createInstance("module.plugins.sqlite2mysql.logic.MigrateLogic")->migrate();
				$this->configObj->redirect("successed");
			}

			if(isset($_POST["rollback"])){
				SOY2Logic::createInstance("module.plugins.sqlite2mysql.logic.MigrateLogic")->rollback($_POST["sqlite"]);
				$this->configObj->redirect("successed");
			}
		}
	}

	function execute(){
		$this->config = SQLMigrateUtil::getConfig();

		parent::__construct();

		$this->addLabel("site_id", array(
			"text" => SOYSHOP_ID
		));

		self::buildMigrateArea();

		self::buildForm();
	}

	private function buildMigrateArea(){
		$dbTypeOnConfigFile = SOYSHOP_DB_TYPE;	//confファイルに記載されているDSNからデータベースのタイプを判断
		$dbTypeOnDb = self::getDBTypeOnAdminDB();	//Siteテーブルに挿入されているDSNからデータベースのタイプを判断

		if($dbTypeOnConfigFile == "mysql" && $dbTypeOnDb == "mysql"){
			$sqliteDbPath = SOYSHOP_SITE_DIRECTORY . ".db/sqlite.db";
			if(!file_exists($sqliteDbPath)) $sqliteDbPath = null;
		}else{
			$sqliteDbPath = null;
		}

		//移行完了	戻すボタンを表示
		DisplayPlugin::toggle("migrate_rollback", strlen($sqliteDbPath));

		$this->addForm("rollback_form");
		$this->addInput("sqlite_dbfile_path", array(
			"name" => "sqlite",
			"value" => $sqliteDbPath
		));


		//移行途中
		DisplayPlugin::toggle("migrate_en_route", ($dbTypeOnConfigFile == "sqlite" && $dbTypeOnDb == "mysql"));

		$this->addLabel("config_file_path", array(
			"text" => SOYSHOP_WEBAPP . "conf/shop/" . SOYSHOP_ID . ".conf.php"
		));

		$this->addLabel("old_dsn", array(
			"text" => SOYSHOP_SITE_DSN
		));

		$this->addLabel("new_dsn", array(
			"text" => SOY2Logic::createInstance("module.plugins.sqlite2mysql.logic.MigrateLogic")->buildDsn()
		));

		$this->addLabel("db_user", array(
			"text" => $this->config["user"]
		));

		//設定内容が登録されていれば表示する。password無しでデータベースを使用することもあるので条件式からパスワードを外す
		DisplayPlugin::toggle("migrate_button", ($dbTypeOnConfigFile == "sqlite" && $dbTypeOnDb == "sqlite" && strlen($this->config["host"]) && strlen($this->config["dbname"])) && strlen($this->config["user"]));

		$this->addForm("migrate_form");
	}

	private function getDBTypeOnAdminDB(){
		$old = SOYAppUtil::switchAdminDsn();
		$dsn = SOY2DAOFactory::create("admin.SiteDAO")->getBySiteId(SOYSHOP_ID)->getDataSourceName();
		SOYAppUtil::resetAdminDsn($old);

		return (strpos($dsn, "mysql") === 0) ? "mysql" : "sqlite";
	}

	private function buildForm(){

		$this->addForm("form");

		$this->addInput("host", array(
			"name" => "Config[host]",
			"value" => $this->config["host"],
			"attr:placeholder" => "localhost"
		));

		$this->addInput("port", array(
			"name" => "Config[port]",
			"value" => $this->config["port"],
			"attr:placeholder" => "3306"
		));

		$this->addInput("dbname", array(
			"name" => "Config[dbname]",
			"value" => $this->config["dbname"],
			"attr:placeholder" => "soyshop_" . SOYSHOP_ID
		));

		$this->addInput("user", array(
			"name" => "Config[user]",
			"value" => $this->config["user"],
			"attr:placeholder" => "root"
		));

		$this->addInput("pass", array(
			"name" => "Config[pass]",
			"value" => $this->config["pass"]
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
