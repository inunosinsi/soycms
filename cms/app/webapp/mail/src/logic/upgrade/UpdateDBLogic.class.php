<?php
SOY2::import("domain.SOYMail_DataSets");
class UpdateDBLogic extends SOY2LogicBase{

	private $directory;
	private $extendDirectory;	//データベースの実行以外の更新
	private $checkVersionLogic;

	//DataSets (soycms_admin_data_sets)でのclass名
	const VERSION_KEY = "SOYMAIL_DB_VERSION";

	/*
	 * 更新ファイルの正規表現
	 * 例：update-1.5.sql
	 */
	const UPDATE_FILE_REGEX = "/^update-([.0-9]+)\\.sql\$/";

	/**
	 * コンストラクタ
	 */
	public function __construct(){
		$this->db = new SOY2DAO();
		$this->checkVersionLogic = SOY2Logic::createInstance("logic.upgrade.CheckVersionLogic");
		self::setDirectory();
	}

	/**
	 * データベースを更新する
	 */
	public function update() {

		$logic = $this->checkVersionLogic;

		//現在のデータベースのバージョンを取得する
		$current = $logic->getCurrentVersion();

		//updateのSQLファイルを取得する
		$sql_files = $logic->getUpdateFiles();

		//現在のデータベースのバージョンより上のupdateを実行する
		foreach($sql_files as $version => $sql_file){
			if($version > $current && strpos($sql_file, ".sql") !== false){
				self::executeSqlFile($sql_file, $version);
				self::registerVersion($version);
			}
		}
	}

	/**
	 * 指定したファイルのSQL文を実行
	 */
	private function executeSqlFile($file,$version=1){

		$sqls = self::getSqlQuery($file);
		foreach($sqls as $sql){
			if(strlen(trim($sql)) < 1) continue;
			try{
				$this->db->executeUpdateQuery($sql, array());
			}catch(Exception $e){
				error_log(var_export($e, true));
				continue;
			}
		}

		//データベース以外の更新
		if(file_exists($this->extendDirectory . "extendUpdate-" . $version . ".php")){
			include_once($this->extendDirectory . "extendUpdate-" . $version . ".php");
		}
	}

	/**
	 * 指定したファイルからコメントを削除したSQL文を配列として取得する
	 * @return Array<string>
	 */
	private function getSqlQuery($file){

		$texts = array();

		$sqls = file_get_contents($this->directory . "/" . $file);
		if(strlen($sqls)){
			//コメント削除
			$sqls = preg_replace("/#.*\$/m", "", $sqls);

			//改行統一
			$sqls = strtr($sqls, array("\r\n" => "\n", "\r" => "\n"));

			$sqls = explode(";", $sqls);
			foreach($sqls as $sql){
				if(strlen(trim($sql)) < 1) continue;
				$texts[] = trim($sql) . ";";
			}
		}

		return $texts;
	}

	/**
	 * バージョン番号を保存する
	 * @param string version
	 */
	private function registerVersion($version){
		try{
			SOYMail_DataSets::put(self::VERSION_KEY, $version);
		}catch(Exception $e){
			error_log(var_export($e, true));
		}
	}

	private function setDirectory(){
		if(!defined("SOYMAIL_DB_MODE")) define("SOYMAIL_DB_MODE", SOYCMS_DB_TYPE);
		$this->directory = SOY2::RootDir() . "logic/upgrade/sql/" . SOYMAIL_DB_MODE ."/";
		$this->extendDirectory = SOY2::RootDir() . "logic/upgrade/extend/";
	}
}
