<?php
SOY2::import("domain.SOYMail_DataSets");
class CheckVersionLogic extends SOY2LogicBase{

	private $directory;

	const VERSION_KEY = "SOYMAIL_DB_VERSION";

	/*
	 * 更新ファイルの正規表現
	 * 例：update-1.5.sql
	 */
	const UPDATE_FILE_REGEX = "/^update-([.0-9]+)\\.sql\$/";

	/**
	 * データベースを更新する必要があるか？を調べる
	 * 更新する必要がある場合はtrueを返す
	 * return boolean
	 */
	function checkVersion(){
		//現在のバージョンを取得し、値がなければ1を返す
		return (self::getCurrentVersion() < self::getUpdateVersion());
	}

	function getCurrentVersion(){
		return SOYMail_DataSets::get(self::VERSION_KEY,0);
	}

	private function getUpdateVersion(){
		return count(self::getUpdateFiles());
	}

	/**
	 * 更新ファイルを取得する
	 * @return Array
	 */
	function getUpdateFiles(){

		self::setDirectory();

		//ディレクトリの実在チェック
		if(strlen($this->directory) < 1){
			throw new Exception("'dir' is empty.");
		}
		if(!is_dir($this->directory)){
			throw new Exception("'dir' ({$this->directory}) does not exist.");
		}

		//ファイル一覧を取得する
		$sql_files = array();
		$match = array();
		$files = scandir($this->directory);
		foreach($files as $file){
			if("." == $file) continue;
			if(".." == $file) continue;
			if(preg_match(self::UPDATE_FILE_REGEX, $file, $match)){
				$sql_files[(int)$match[1]] = $file;
			}
		}

		//versionの昇順に並べ替える
		ksort($sql_files);

		return $sql_files;
	}

	private function setDirectory(){
		if(!defined("SOYMAIL_DB_MODE")) define("SOYMAIL_DB_MODE", SOYCMS_DB_TYPE);
		$this->directory = SOY2::RootDir() . "logic/upgrade/sql/" . SOYMAIL_DB_MODE ."/";
	}
}
