<?php

class MigrateLogic extends SOY2LogicBase {

	private $sqlitePath;
	private $backupDir;
	private $pdo;

	function __construct(){
		$this->sqlitePath = UserInfoUtil::getSiteDirectory() . ".db/sqlite.db";
		$this->backupDir = UserInfoUtil::getSiteDirectory() . ".db/backup/";
		define("RECORD_LIMIT", 100);
	}

	function migrate(){
		set_time_limit(0);

		//既にSQLiteのデータベースがあれば、バックアップをとってから削除
		if(file_exists($this->sqlitePath)){
			if(!file_exists($this->backupDir)){
				mkdir($this->backupDir);
			}
			$i = 0;
			for(;;){
				if(!file_exists($this->backupDir . "sqlite.db.backup" . $i)){
					rename($this->sqlitePath, $this->backupDir . "sqlite.db.backup" . $i);
					break;
				}
				$i++;
			}
		}
		touch($this->sqlitePath);
		$this->pdo = new PDO("sqlite:" . $this->sqlitePath);

		$sqls = file_get_contents(SOY2::rootDir() . "sql/init_site_sqlite.sql");
		if(preg_match_all('/CREATE.*?;/mis', $sqls, $tmp)){
            if(count($tmp[0])){
                foreach($tmp[0] as $sql){
					$this->pdo->query($sql);

					//データを挿入
					if(strpos($sql, "create table") !== false){
						preg_match('/create table (.*)\(/', $sql, $tmp);
						if(isset($tmp[1])){
							$label = trim($tmp[1]);
							switch($label){
								case "EntryHistory":
								case "Template":
								case "TemplateHistory":
								case "soycms_data_sets":
									$isMigrate = false;
									break;
								default:
									$isMigrate = true;
							}

							if(!$isMigrate) continue;

							include_once(dirname(__FILE__) . "/table/" . $label . ".php");
							$func = "register" . $label;
							$func(self::buildStatememt($sql));
						}
					}
                }
            }

			//カスタムフィールド
			if(file_exists(UserInfoUtil::getSiteDirectory() . "/.plugin/CustomField.active")){
				include_once(dirname(__FILE__) . "/table/CustomField.php");
				registerCustomField();
			}

			//ブログ記事SEOプラグイン
			if(file_exists(UserInfoUtil::getSiteDirectory() . "/.plugin/soycms_entry_info.active")){
				include_once(dirname(__FILE__) . "/table/EntryInfo.php");
				registerEntryInfo();
			}

			/** @ToDo gravatar **/
			/** @ToDo read_entry_count **/
			/** @ToDo record_dead_link **/
			/** @ToDo soycms_like_button **/
			/** @ToDo url_shortener **/

			//最後にadmin.Siteの方のdata_source_nameの値を変更
			$siteId = UserInfoUtil::getSiteId();

			$old = CMSUtil::switchDsn();
			$siteDao = SOY2DAOFactory::create("admin.SiteDAO");
			$site = $siteDao->getById($siteId);
			$site->setDataSourceName("sqlite:" . $this->sqlitePath);
			$siteDao->update($site);

			CMSUtil::resetDsn($old);

			//SQLite用のfile.dbを作成する
			$fileDbPath = SOY2::RootDir()."db/file.db";
			unlink($fileDbPath);
			touch($fileDbPath);

			$filePdo = new PDO("sqlite:".$fileDbPath);
			$sql = file_get_contents(CMS_SQL_DIRECTORY."init_file_sqlite.sql");
			try{
				$filePdo->exec($sql);
			}catch(Exception $e){
				//
			}
        }
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
}
