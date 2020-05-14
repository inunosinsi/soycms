<?php

class MigrateLogic extends SOY2LogicBase {

	private $pdo;

	function __construct(){
		SOY2::import("module.plugins.sqlite2mysql.util.SQLMigrateUtil");
		define("RECORD_LIMIT", 100);
	}

	function buildDsn(){
		$config = SQLMigrateUtil::getConfig();
		$dsn = "mysql:host=" . $config["host"] . ";";
		$dsn .= "dbname=" . $config["dbname"] . ";";
		if(strlen($config["port"])) $dsn .= "port=" . $config["port"] . ";";
		$dsn .= "charset=utf8";
		return $dsn;
	}

	function migrate(){
		set_time_limit(0);

		$dsn = self::buildDsn();
		$config = SQLMigrateUtil::getConfig();
		// $this->pdo = new PDO(
		// 	$dsn, $config["user"],
		// 	$config["pass"],
		// 	array(
        //     	PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        //     	PDO::ATTR_EMULATE_PREPARES => false,
        // 	));
		$this->pdo = new PDO($dsn, $config["user"], $config["pass"]);

		$sqls = file_get_contents(SOY2::rootDir() . "logic/init/mysql.sql");

		//テーブルの削除
		if(preg_match_all('/DROP.*?;/mis', $sqls, $tmp)){
			if(count($tmp[0])){
				foreach($tmp[0] as $sql){
					try{
						$this->pdo->query($sql);
					}catch(exception $e){
						//
					}
				}
			}
		}

		//ここから移行
		if(preg_match_all('/CREATE.*?;/mis', $sqls, $tmp)){
			if(count($tmp[0])){
                foreach($tmp[0] as $sql){
					$this->pdo->query($sql);

					//データを挿入
					if(strpos($sql, "create table") !== false){
						preg_match('/create table soyshop_(.*)\(/', $sql, $tmp);
						if(isset($tmp[1])){
							$label = trim($tmp[1]);
							switch($label){
								case "breadcrumb":
								case "favorite_item":
									$pluginId = "common_" . $label;
									break;
								case "item_review":
									$pluginId = $label;
									break;
								case "review_point":
									$pluginId = "item_review";
									break;
								default:	//プラグイン由来のテーブルではないもの
									$pluginId = null;
							}
							if(isset($pluginId) && !SOYShopPluginUtil::checkIsActive($pluginId)){	//プラグインが持つテーブルの場合は移行せずにテーブルを削除することがある
								self::dropTable("soyshop_" . $label);
							}else{
								if(file_exists(dirname(__FILE__) . "/table/" . $label . ".php")){
									include_once(dirname(__FILE__) . "/table/" . $label . ".php");
									$func = "register_" . $label;
									$func(self::buildStatememt($sql));
								}
							}
						}
					}
                }
            }

			/** 未対応のプラグインはconfigページに記載 **/

			//最後にadmin.Siteの方のdata_source_nameの値を変更
			self::changeDsnOnDatabase($dsn);

			//パスワードを破棄
			$config["pass"] = "";
			SQLMigrateUtil::saveConfig($config);
        }
	}

	private function dropTable($tableName){
		$this->pdo->query("DROP TABLE " . $tableName . ";");
	}

	private function buildStatememt($sql){
		$tableName = self::getTableNameBySQL($sql);
		$columns = self::splitColumns($sql);
		return $this->pdo->prepare("INSERT INTO " . $tableName . " (" . implode(",", $columns) . ") VALUES (:" . implode(",:", $columns) . ");");
	}

	private function getTableNameBySQL($sql){
		$rows = explode("\n", $sql);

		foreach($rows as $row){
			$row = trim($row);
			if(strpos($row, "create table") !== false || strpos($row, "CREATE TABLE") !== false){
				$tableName = trim(str_replace(array("create table", "CREATE TABLE"), "", $row));
				return trim(str_replace("(", "", $tableName));
			}
		}

		return "";
	}

	private function splitColumns($sql){
		$rows = explode("\n", $sql);

		$columns = array();
		foreach($rows as $row){
			$row = trim($row);
			if(strpos($row, "create table") === 0 || strpos($row, ")") === 0 || strpos($row, "unique") === 0 || strpos($row, "UNIQUE") === 0) continue;
			$split = explode(" ", $row);
			$columns[] = trim($split[0]);
		}

		return $columns;
	}

	function rollback($sqliteDbPath){
		self::changeDsnOnDatabase("sqlite:" . $sqliteDbPath);
	}

	private function changeDsnOnDatabase($dsn){
		//最後にadmin.Siteの方のdata_source_nameの値を変更
		$old = SOYAppUtil::switchAdminDsn();
		$siteDao = SOY2DAOFactory::create("admin.SiteDAO");
		$site = $siteDao->getBySiteId(SOYSHOP_ID);
		$site->setDataSourceName($dsn);
		$siteDao->update($site);
		SOYAppUtil::resetAdminDsn($old);

		//SOYShopのデータベースの方の値も変更
		$old = SOYAppUtil::switchAppMode("shop");
		$shopDao = SOY2DAOFactory::create("SOYShop_SiteDAO");
		$site = $shopDao->getBySiteId(SOYSHOP_ID);
		$site->setDsn($dsn);
		$shopDao->update($site);
		SOYAppUtil::resetAppMode($old);
	}
}
