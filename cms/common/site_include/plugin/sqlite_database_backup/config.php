<?php
class SqliteDatabaseBackupConfigPage extends WebPage{

	private $pluginObj;
	private $sitePath;
	private $dbFile;
	private $backupDir;

	const HASH_ALGO = "sha512";
	const HASH_SALT = "a723f4bf4d023dacaff800376408b448";

	function SqliteDatabaseBackupConfigPage(){
		$this->sitePath = CMSPlugin::getSiteDirectory();
		$this->dbFile = $this->sitePath . ".db/sqlite.db";
	}

	function doPost(){

		if(soy2_check_token()){

			//データベースロックを解除する
			if(isset($_POST["release"])){

				//sqlite.dbをバックアップディレクトリにコピー
				$temp = $this->backupDir."sqlite.db";
				copy($this->dbFile,$temp);

				//バックアップ用ファイルとしてコピー
				$copyFile = $this->backupDir."sqlite.db-".date("Ymd-His");
				copy($temp,$copyFile);

				//バックアップ用のディレクトリ内のコピーしたファイルを書き戻す
				rename($temp,$this->dbFile);

				CMSPlugin::redirectConfigPage();
			}

			//バックアップファイルを削除する
			if(isset($_POST["delete"]) && isset($_POST["target"]) && strlen($_POST["target"])){
				$hash = $_POST["target"];
				$files = scandir($this->backupDir);
				foreach($files as $file){
					if(strpos($file,".") === 0) continue;
					if(hash(SqliteDatabaseBackupConfigPage::HASH_ALGO, SqliteDatabaseBackupConfigPage::HASH_SALT.$file) == $hash){
						unlink($this->backupDir.$file);
						break;
					}
				}
				CMSPlugin::redirectConfigPage();
			}
		}

		//バックアップファイルをダウンロードする
		if(isset($_POST["download"]) && isset($_POST["target"]) && strlen($_POST["target"])){
			$hash = $_POST["target"];
			$files = scandir($this->backupDir);
			foreach($files as $file){
				if(strpos($file,".") === 0) continue;
				if(hash(SqliteDatabaseBackupConfigPage::HASH_ALGO, SqliteDatabaseBackupConfigPage::HASH_SALT.$file) == $hash){
					error_reporting(0);
					setcookie("downloaded", "yes", time()+2);
					header("Cache-Control: no-cache");
					header("Pragma: no-cache");
					header("Expires: 0");
					header("Content-Disposition: attachment; filename=".$file);
					header("Content-Type: application/sqlite");
					ob_start();
					$ob = ob_start("ob_gzhandler");
					readfile($this->backupDir.$file);
					if($ob) ob_end_flush();
					header("Content-Length: ".ob_get_length());
					ob_end_flush();
				}
			}
			exit;
		}

	}

	function execute(){

		//コンストラクタではpluginObjは初期化されてないのでここで行う必要がある
		//親メソッドのコンストラクタより先に呼び出さないとdoPostで使えない
		$this->backupDir = $this->sitePath . $this->pluginObj->backupDir;
		//バックアップディレクトリがなければ作る
		if(!file_exists($this->backupDir)){
			mkdir($this->backupDir);
		}

		WebPage::WebPage();

		// jQuery Cookie
		HTMLHead::addScript("jquery.cookile.js",array(
			"type" => "text/JavaScript",
			"src" => SOY2PageController::createRelativeLink("js/etc/jquery.cookie.js")."?".SOYCMS_BUILD_TIME
		));

		//sqliteでないと動作させない
		$this->addModel("sqlite",array(
			"visible" => (SOYCMS_DB_TYPE==="sqlite")
		));

		$this->addLabel("backup_dir",array(
			"text" => $this->backupDir,
		));

		$this->createAdd("backup_file_list", "SqliteDatabaseBackupConfigPage_BackupFileList", array(
			"list" => scandir($this->backupDir),
			"baseDir" => $this->backupDir,
		));

		$this->addForm("release_form",array());
	}

	function getTemplateFilePath(){
		return dirname(__FILE__)."/config.html";
	}

	function getPluginObj() {
		return $this->pluginObj;
	}
	function setPluginObj($pluginObj) {
		$this->pluginObj = $pluginObj;
	}
}

class SqliteDatabaseBackupConfigPage_BackupFileList extends HTMLList{
	private $baseDir;

	protected function populateItem($entity){

		// .で始まるファイルは無視する
		if(strpos($entity,".")===0)return false;

		$this->addLabel("file", array(
			"text" => $entity,
		));
		$this->addLabel("datetime", array(
			"text" => file_exists($this->baseDir.$entity) ? date("Y-m-d H:i:s", filemtime($this->baseDir.$entity)) : "-",
		));
		$this->addLabel("size", array(
			"text" => file_exists($this->baseDir.$entity) ? CMSUtil::GetHumanReadableSize(filesize($this->baseDir.$entity)) : "-",
		));

		//ダウンロードボタン
		$this->addForm("download_form",array(
			"onsubmit" => '',
		));
		//ファイルを直接指定すると他のディレクトリの任意のファイルまでダウンロード可能になる可能性があるのでやるべきではない
		$this->addInput("target" ,array(
			"name" => "target",
			"value" => hash(SqliteDatabaseBackupConfigPage::HASH_ALGO, SqliteDatabaseBackupConfigPage::HASH_SALT.$entity),
		));

		//削除ボタン
		$this->addForm("delete_form");
		//ファイルを直接指定すると他のディレクトリの任意のファイルまで削除可能になる可能性があるのでやるべきではない
		$this->addInput("target" ,array(
			"name" => "target",
			"value" => hash(SqliteDatabaseBackupConfigPage::HASH_ALGO, SqliteDatabaseBackupConfigPage::HASH_SALT.$entity),
		));
	}

	public function setBaseDir($v){
		$this->baseDir = $v;
	}
}
