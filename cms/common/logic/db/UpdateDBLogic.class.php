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
class UpdateDBLogic extends SOY2LogicBase{

	private $target;
	private $dir;
	private $db;
	private $extendDirectory;	//データベースの実行以外の更新

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
	 * データベースを更新する
	 * サイト側のDBの更新の場合は$siteDirに値がある
	 */
	public function update($siteDir = null) {

		//現在のデータベースのバージョンを取得する
		$current_version = $this->getCurrentVersion();

		//updateのSQLファイルを取得する
		$sql_files = $this->getUpdateFiles();

		//現在のデータベースのバージョンより上のupdateを実行する
		foreach($sql_files as $sql_file){
			//ファイル名からバージョンを取得するように変更→環境によってはupdate-n.sql以外のファイルが混じっていることがあるため
			preg_match('/update-(\d*).sql/', $sql_file, $tmp);
			if(!isset($tmp[1]) || !is_numeric($tmp[1])) continue;
			$version = (int)$tmp[1];

			if($version > $current_version && strpos($sql_file,".sql")!==false){
				$this->executeSqlFile($sql_file, $version , $siteDir);
				$this->registerVersion($version);
			}
		}

	}

	/**
	 * 現在のデータベースのバージョンを返す
	 * @return int
	 */
	public function getCurrentVersion(){

		try{
			switch($this->target){
				case "site":
					$version = DataSets::get(self::VERSION_KEY);
					break;
				case "admin":
					$version = AdminDataSets::get(self::VERSION_KEY);
					break;
				default:
					$version = null;
			}
		}catch(Exception $e){
			$version = null;
		}

		return $version;
	}

	/**
	 * 更新ファイルの最大バージョンを返す
	 * @return int
	 */
	public function getUpdateVersion(){
		$files = $this->getUpdateFiles();
		if($files && is_array($files) && count($files)){
			$files = array_keys($files);
			return array_pop($files);
		}
		return 0;
	}

	/**
	 * 更新が必要かどうか
	 * @return boolean
	 */
	public function hasUpdate(){
		$current = $this->getCurrentVersion();
		$update  = $this->getUpdateVersion();
		return ($update > 0 && $update > $current);
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
	 * 指定したファイルのSQL文を実行
	 */
	private function executeSqlFile($file, $version=1, $siteDir=null){

		$sqls = $this->getSqlQuery($file);
		foreach($sqls as $sql){
			if(strlen(trim($sql))<1)continue;
			try{
				$this->db->executeUpdateQuery($sql,array());
			}catch(Exception $e){
				error_log(var_export($e,true));
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

		$sqls = file_get_contents($this->dir."/".$file);
		if(strlen($sqls)){
			//コメント削除
			$sqls = preg_replace("/#.*\$/m", "", $sqls);

			//改行統一
			$sqls = strtr($sqls, array("\r\n" => "\n", "\r" => "\n"));

			$sqls = explode(";",$sqls);
			foreach($sqls as $sql){
				if(strlen(trim($sql))<1)continue;
				$texts[] = $sql.";";
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
				$this->extendDirectory = CMS_SQL_DIRECTORY."extend/".$target."/";
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
