<?php

/**
 * データベースの半自動更新ロジック
 *
 * 使用例
 * $logic = SOY2LogicContainer::get("logic.db.UpdateDBLogic", array(
 * 		"target" => "admin"
 * ));
 * $logic->update();
 */
class DBVersionLogic extends SOY2LogicBase{

	private $target;
	private $dir;
	private $db;

	//DataSets (soycms_admin_data_sets)でのclass名
	const VERSION_KEY = "SOYCMS_DB_VERSION";

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
	}

	/**
	 * 現在のデータベースのバージョンを返す
	 * @return int
	 */
	public function getCurrentSQLVersion(){
		$files = $this->getUpdateFiles();
		end($files);
		return key($files);
	}

	/**
	 * 現在のデータベースのバージョン番号を保存する
	 * @param string version
	 * @throw Exception
	 */
	public function registerCurrentSQLVersion(){
		$this->registerVersion($this->getCurrentSQLVersion());
	}

	/**
	 * 更新ファイルを取得する
	 * @return Array
	 */
	private function getUpdateFiles(){

		//ディレクトリの実在チェック
		if(strlen($this->dir)<1){
			throw new Exception("'dir' is empty.");
		}
		if(!is_dir($this->dir)){
			throw new Exception("'dir' ({$this->dir}) does not exist.");
		}

		//ファイル一覧を取得する
		$sql_files = array();
		$match = array();
		$files = scandir($this->dir);
		foreach($files as $file){
			if("." == $file)continue;
			if(".." == $file)continue;
			if(preg_match(self::UPDATE_FILE_REGEX, $file, $match)){
				$sql_files[(int)$match[1]] = $file;
			}
		}

		//versionの昇順に並べ替える
		ksort($sql_files);

		return $sql_files;
	}

	/**
	 * バージョン番号を保存する
	 * @param string version
	 */
	private function registerVersion($version){
		try{
			switch($this->target){
				case "site":
					DataSets::put(self::VERSION_KEY,$version);
					break;
				case "admin":
					AdminDataSets::put(self::VERSION_KEY,$version);
					break;
			}
		}catch(Exception $e){
			error_log(var_export($e,true));
		}
	}

	/**
	 * 更新対象を指定
	 * @param string "admin" | "site"
	 */
	public function setTarget($target){
		$this->target = $target;

		switch($target){
			case "site":
			case "admin":
				$this->dir = CMS_SQL_DIRECTORY."update/".$target."/".SOYCMS_DB_TYPE."/";
		}
		switch($target){
			case "site":
				SOY2DAOFactory::importEntity("cms.DataSets");
				break;
			case "admin":
				SOY2DAOFactory::importEntity("admin.AdminDataSets");
				break;
		}
	}
}
