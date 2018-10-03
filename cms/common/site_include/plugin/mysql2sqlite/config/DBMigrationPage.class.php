<?php

class DBMigrationPage extends WebPage {

	private $pluginObj;

	const STATUS_MIGRATE_NG = -1;		//SQLite版のため使用不可
	const STATUS_MIGRATE_BEFORE = 0;	//移行前
	const STATUS_MIGRATE_MIDDLE = 1;	//移行途中

	function __construct(){

	}

	function doPost(){
		if(soy2_check_token()){
			if(isset($_POST["migrate"])){
				SOY2Logic::createInstance("site_include.plugin.mysql2sqlite.logic.MigrateLogic")->migrate();
			}

			//ADMINデータベースのdatasourceを戻す
			if(isset($_POST["return"])){
				$siteId = UserInfoUtil::getSiteId();

				$old = CMSUtil::switchDsn();
				$siteDao = SOY2DAOFactory::create("admin.SiteDAO");
				$site = $siteDao->getById($siteId);
				$site->setDataSourceName($this->pluginObj->getOldDataSource());
				try{
					$siteDao->update($site);
				}catch(Exception $e){
					var_dump($e);
				}

				CMSUtil::resetDsn($old);
			}

			CMSPlugin::redirectConfigPage();
		}
	}

	function execute(){
		parent::__construct();

		$status = self::getMigrateStatus();
		DisplayPlugin::toggle("ng", $status == self::STATUS_MIGRATE_NG);
		DisplayPlugin::toggle("before", $status == self::STATUS_MIGRATE_BEFORE);
		DisplayPlugin::toggle("middle", $status == self::STATUS_MIGRATE_MIDDLE);

		$this->addForm("return_form");

		$this->addLabel("front_controller_path", array(
			"text" => UserInfoUtil::getSiteDirectory() . "index.php"
		));

		$this->addLabel("migrate_before_code", array(
			"text" => self::buildSampleCode("mysql")
		));

		$this->addLabel("migrate_after_code", array(
			"text" => self::buildSampleCode("sqlite")
		));

		$this->addForm("form");
	}

	private function getMigrateStatus(){
		if(SOYCMS_DB_TYPE === "sqlite") return self::STATUS_MIGRATE_NG;

		//移行途中
		if(strpos(SOY2DAOConfig::Dsn(), "sqlite") === 0) return self::STATUS_MIGRATE_MIDDLE;

		//後はすべて移行前
		return self::STATUS_MIGRATE_BEFORE;
	}

	private function buildSampleCode($dbType){
		$code = array();

		if($dbType == "mysql"){
			$code[] = "define(\"_SITE_DSN_\",\"" . $this->pluginObj->getOldDataSource() . "\");";
			$code[] = "define(\"_SITE_DB_FILE_\",_SITE_ROOT_.\"/.db/mysql.db\");";
		}else{
			$code[] = "define(\"_SITE_DSN_\",\"sqlite:\" . _SITE_ROOT_.\"/.db/sqlite.db\");";
			$code[] = "define(\"_SITE_DB_FILE_\",_SITE_ROOT_.\"/.db/sqlite.db\");";
		}

		return implode("\n", $code);
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
